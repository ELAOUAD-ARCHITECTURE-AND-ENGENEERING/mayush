const { chromium } = require('playwright');

const baseUrl = process.env.BROWSER_QA_BASE_URL || 'http://127.0.0.1:8001';
const credentials = {
  customer: { email: 'qa-customer@example.test', password: 'Password123!' },
  seller: { email: 'qa-seller@example.test', password: 'Password123!' },
  admin: { email: 'qa-admin@example.test', password: 'Password123!' },
};

const results = [];
const flowTimeoutMs = Number(process.env.BROWSER_QA_FLOW_TIMEOUT_MS || 12000);
const navigationTimeoutMs = Number(process.env.BROWSER_QA_NAV_TIMEOUT_MS || 10000);

async function addResult(name, path, status, details = {}, consoleErrors = []) {
  results.push({
    name,
    url: `${baseUrl}${path}`,
    status,
    details,
    consoleErrors: consoleErrors.slice(-5),
  });
}

function hasServerError(text) {
  return /Server Error|SQLSTATE|Exception|Trace|Whoops/i.test(text);
}

async function pageText(page) {
  return (await page.locator('body').textContent({ timeout: 7000 }).catch(() => '')) || '';
}

async function visit(page, path) {
  const targetUrl = `${baseUrl}${path}`;
  const response = await page.goto(targetUrl, {
    waitUntil: 'domcontentloaded',
    timeout: navigationTimeoutMs,
  }).catch((error) => ({ error }));

  await page.waitForLoadState('domcontentloaded', { timeout: 2500 }).catch(() => {});

  if (response?.error) {
    const bodyExists = await page.locator('body').count().catch(() => 0);
    const reachedTarget = page.url().startsWith(targetUrl);
    if (bodyExists && reachedTarget) {
      return { timedOutAfterRender: true, status: () => null };
    }
  }

  return response;
}

async function runFlow(name, page, path, callback) {
  console.error(`[browser-qa] starting ${name}`);
  const errors = [];
  const listener = (message) => {
    if (message.type() === 'error') {
      errors.push(message.text());
    }
  };

  page.on('console', listener);

  try {
    const response = await visit(page, path);
    const text = await pageText(page);
    const statusCode = typeof response?.status === 'function' ? response.status() : null;
    const baseFailure = response?.error
      ? response.error.message
      : statusCode && statusCode >= 400
        ? `HTTP ${statusCode}`
        : hasServerError(text)
          ? 'Server error text rendered'
          : null;

    if (baseFailure) {
      await addResult(name, path, 'FAIL', { error: baseFailure, currentUrl: page.url() }, errors);
      return;
    }

    const outcome = await Promise.race([
      callback({ page, text, statusCode, errors }),
      new Promise((_, reject) => setTimeout(() => reject(new Error(`flow timed out after ${flowTimeoutMs}ms`)), flowTimeoutMs)),
    ]);
    await addResult(name, path, outcome.status || 'PASS', {
      statusCode,
      currentUrl: page.url(),
      ...(outcome.details || {}),
    }, errors);
  } catch (error) {
    await addResult(name, path, 'FAIL', {
      error: error.message,
      currentUrl: page.url(),
    }, errors);
  } finally {
    page.off('console', listener);
    console.error(`[browser-qa] finished ${name}`);
  }
}

async function login(page, role) {
  const loginPath = role === 'seller' ? '/seller/login' : '/users/login';
  await visit(page, loginPath);
  const { email, password } = credentials[role];

  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);
  await page.getByRole('button', { name: /login/i }).first().click({
    timeout: 7000,
    noWaitAfter: true,
  });
  await page.waitForTimeout(1800);
  await page.waitForLoadState('domcontentloaded', { timeout: 3000 }).catch(() => {});

  const text = await pageText(page);
  const currentUrl = page.url();
  const stillOnLoginRoute = currentUrl.endsWith(loginPath) || currentUrl.includes(`${loginPath}?`);
  const emailInputCount = await page.locator('input[name="email"]').count().catch(() => 0);
  const stillShowsLoginForm = /Login to your account|Welcome Back/i.test(text) && emailInputCount > 0;

  if (stillOnLoginRoute && stillShowsLoginForm) {
    throw new Error(`${role} login stayed on login form`);
  }
}

async function logout(page) {
  await visit(page, '/logout').catch(() => {});
  await page.context().clearCookies().catch(() => {});
}

function withTimeout(promise, label, timeoutMs = flowTimeoutMs) {
  return Promise.race([
    promise,
    new Promise((_, reject) => setTimeout(() => reject(new Error(`${label} timed out after ${timeoutMs}ms`)), timeoutMs)),
  ]);
}

async function clickFirstVisible(page, selectors) {
  for (const selector of selectors) {
    const locator = page.locator(selector).first();
    if (await locator.count()) {
      if (await locator.isVisible().catch(() => false)) {
        try {
          await locator.click({ timeout: 7000, noWaitAfter: true });
        } catch (error) {
          if (!String(error.message || error).includes('click action done')) {
            throw error;
          }
        }
        return selector;
      }
    }
  }
  return null;
}

async function newQaPage(browser) {
  const context = await browser.newContext({
    baseURL: baseUrl,
    ignoreHTTPSErrors: true,
  });
  const page = await context.newPage();
  page.setDefaultTimeout(10000);
  page.setDefaultNavigationTimeout(20000);

  return { context, page };
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const guestSession = await newQaPage(browser);
  const page = guestSession.page;

  await runFlow('public homepage', page, '/', async ({ text }) => ({
    status: text.includes('QA Seller Shop') || text.includes('QA Category') ? 'PASS' : 'PARTIAL',
    details: { expectedSeededContentVisible: text.includes('QA Seller Shop') || text.includes('QA Category') },
  }));

  await runFlow('registration page smoke', page, '/users/registration', async ({ page, text }) => {
    const email = `browser-qa-${Date.now()}@example.test`;
    await page.locator('#reg-form').evaluate((form, submittedEmail) => {
      const setValue = (selector, value) => {
        const input = form.querySelector(selector);
        if (!input) {
          return;
        }

        input.value = value;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      };

      setValue('input[name="name"]', 'Browser QA User');
      setValue('input[name="email"]', submittedEmail);
      setValue('input[name="phone"]', '5550009012');
      setValue('input[name="country_code"]', '+1');
      setValue('input[name="verification_method"]', 'email');
      setValue('input[name="password"]', 'Password123!');
      setValue('input[name="password_confirmation"]', 'Password123!');

      const terms = form.querySelector('input[name="checkbox_example_1"], input[type="checkbox"]');
      if (terms) {
        terms.checked = true;
        terms.dispatchEvent(new Event('change', { bubbles: true }));
      }

      form.submit();
    }, email);
    await page.waitForTimeout(1000);
    const after = await pageText(page);
    const finalUrl = page.url();
    await logout(page);
    return {
      status: hasServerError(after) ? 'FAIL' : 'PASS',
      details: { submittedEmail: email, currentUrl: finalUrl, pageStillShowsForm: /Create an account/i.test(after) },
    };
  });

  await runFlow('customer login/logout', page, '/users/login', async ({ page }) => {
    await withTimeout(login(page, 'customer'), 'customer login');
    const loggedInText = await pageText(page);
    await logout(page);
    return {
      status: /Dashboard|Logout|QA Customer/i.test(loggedInText) ? 'PASS' : 'PARTIAL',
      details: { loggedInUrl: page.url() },
    };
  });

  await runFlow('password reset page smoke', page, '/password/reset', async ({ text }) => ({
    status: /Forgot|Reset|Email/i.test(text) ? 'PASS' : 'PARTIAL',
  }));

  await runFlow('contact form submit', page, '/contact-us', async ({ page, text }) => {
    if (!/Contact|Message|Email/i.test(text)) {
      return { status: 'FAIL', details: { error: 'contact form content not visible' } };
    }
    const contactForm = page.locator('textarea[name="message"], textarea[name="content"], textarea[name="query"]').first()
      .locator('xpath=ancestor::form[1]');
    await contactForm.locator('input[name="name"], input[name="first_name"]').first().fill('Browser QA');
    await contactForm.locator('input[name="email"]').first().fill('browser-qa@example.test');
    const subject = contactForm.locator('input[name="subject"]');
    if (await subject.count()) {
      await subject.first().fill('Browser QA Contact');
    }
    await contactForm.locator('textarea[name="message"], textarea[name="content"], textarea[name="query"]').first().fill('Browser QA contact message');
    await contactForm.evaluate((form) => form.submit());
    await page.waitForTimeout(1000);
    const after = await pageText(page);
    return {
      status: hasServerError(after) ? 'FAIL' : 'PASS',
      details: { currentUrl: page.url(), hasValidationOrSuccess: /success|sent|required|message/i.test(after) },
    };
  });

  await runFlow('search and filters', page, '/search?keyword=QA', async ({ text }) => ({
    status: text.includes('QA Stocked Product') || text.includes('QA Category') ? 'PASS' : 'PARTIAL',
    details: { seededProductVisible: text.includes('QA Stocked Product') },
  }));

  await runFlow('product detail', page, '/product/qa-stocked-product', async ({ text }) => ({
    status: text.includes('QA Stocked Product') ? 'PASS' : 'FAIL',
    details: { productNameVisible: text.includes('QA Stocked Product') },
  }));

  await runFlow('stock alert subscription', page, '/product/qa-out-of-stock-product', async ({ page, text }) => {
    const hasNotify = /Notify me|available|stock alert|out of stock/i.test(text);
    const email = page.locator('form[action*="stock-alert"] input[name="email"]');
    if (await email.count()) {
      await email.first().fill('browser-qa-stock@example.test');
    }
    const clicked = await clickFirstVisible(page, [
      'form[action*="stock-alert"] button[type="submit"]',
      'button:has-text("Notify")',
      'button:has-text("Notify me")',
    ]);
    await page.waitForTimeout(1500);
    const after = await pageText(page);
    return {
      status: hasNotify && (clicked || /already|success|notify/i.test(after)) ? 'PASS' : 'PARTIAL',
      details: { notifyUiVisible: hasNotify, clickedSelector: clicked },
    };
  });

  await guestSession.context.close();

  const customerSession = await newQaPage(browser);
  const customerPage = customerSession.page;

  try {
    await withTimeout(login(customerPage, 'customer'), 'customer login');
    await addResult('customer auth setup', '/users/login', 'PASS', { currentUrl: customerPage.url() });
  } catch (error) {
    await addResult('customer auth setup', '/users/login', 'FAIL', { error: error.message, currentUrl: customerPage.url() });
  }

  await runFlow('add to cart', customerPage, '/product/qa-stocked-product', async ({ page }) => {
    const clicked = await clickFirstVisible(page, [
      'button:has-text("Add to cart")',
      'button:has-text("Add To Cart")',
      'a:has-text("Add to cart")',
      'a:has-text("Add To Cart")',
    ]);
    await page.waitForTimeout(1500);
    const after = await pageText(page);
    return {
      status: clicked && /cart|added|checkout|View Cart/i.test(after) ? 'PASS' : 'PARTIAL',
      details: { clickedSelector: clicked, cartFeedbackVisible: /cart|added|checkout|View Cart/i.test(after) },
    };
  });

  await runFlow('cart to checkout', customerPage, '/cart', async ({ page, text }) => {
    const clicked = await clickFirstVisible(page, [
      'a:has-text("Proceed to Checkout")',
      'button:has-text("Proceed to Checkout")',
      'a[href*="checkout"]',
    ]);
    await page.waitForLoadState('domcontentloaded', { timeout: 8000 }).catch(() => {});
    await page.waitForTimeout(1000);
    const after = await pageText(page);
    return {
      status: /checkout/i.test(page.url()) || /checkout|shipping|payment/i.test(after) ? 'PASS' : 'PARTIAL',
      details: { clickedSelector: clicked, beforeHadCartText: /cart/i.test(text), finalUrl: page.url() },
    };
  });

  await runFlow('buy now', customerPage, '/product/qa-stocked-product', async ({ page }) => {
    const clicked = await clickFirstVisible(page, [
      'button:has-text("Buy Now")',
      'a:has-text("Buy Now")',
      'button:has-text("Buy now")',
      'a:has-text("Buy now")',
    ]);
    await page.waitForLoadState('domcontentloaded', { timeout: 8000 }).catch(() => {});
    await page.waitForTimeout(1000);
    const after = await pageText(page);
    return {
      status: clicked && (/checkout/i.test(page.url()) || /checkout|shipping|payment/i.test(after)) ? 'PASS' : 'PARTIAL',
      details: { clickedSelector: clicked, finalUrl: page.url() },
    };
  });

  await runFlow('follow seller', customerPage, '/shop/qa-seller-shop', async ({ page, text }) => {
    const clicked = await clickFirstVisible(page, [
      'button:has-text("Follow")',
      'button:has-text("Follow Seller")',
      'form[action*="followed-seller-store"] button',
      'form[action*="followed-seller-store"] input[type="submit"]',
    ]);
    await page.waitForTimeout(1500);
    const after = await pageText(page);
    return {
      status: clicked || /follow/i.test(text) ? 'PASS' : 'PARTIAL',
      details: { clickedSelector: clicked, postClickHasFollowText: /follow|unfollow/i.test(after) },
    };
  });

  await runFlow('customer purchase history', customerPage, '/purchase_history', async ({ text }) => ({
    status: text.includes('QA-ORDER-1001') || text.includes('QA Stocked Product') ? 'PASS' : 'PARTIAL',
    details: { seededOrderVisible: text.includes('QA-ORDER-1001') || text.includes('QA Stocked Product') },
  }));

  await runFlow('destructive DELETE-form surfaces smoke', customerPage, '/profile', async ({ page, text }) => {
    const html = await page.content();
    return {
      status: html.includes('_method') || /delete|address|account/i.test(text) ? 'PASS' : 'PARTIAL',
      details: {
        containsDeleteOverride: html.includes('name="_method"') && /DELETE/i.test(html),
        containsAccountText: /delete|account|address/i.test(text),
      },
    };
  });

  await customerSession.context.close();

  const sellerSession = await newQaPage(browser);
  const sellerPage = sellerSession.page;

  try {
    await withTimeout(login(sellerPage, 'seller'), 'seller login');
    await addResult('seller auth setup', '/seller/login', 'PASS', { currentUrl: sellerPage.url() });
  } catch (error) {
    await addResult('seller auth setup', '/seller/login', 'FAIL', { error: error.message, currentUrl: sellerPage.url() });
  }

  await runFlow('seller dashboard', sellerPage, '/seller/dashboard', async ({ text }) => ({
    status: /Dashboard|Products|Orders|QA Seller/i.test(text) ? 'PASS' : 'PARTIAL',
  }));

  await runFlow('seller notes list', sellerPage, '/seller/note', async ({ text }) => ({
    status: /Note|Create|Title|Description/i.test(text) ? 'PASS' : 'PARTIAL',
  }));

  await sellerSession.context.close();

  const adminSession = await newQaPage(browser);
  const adminPage = adminSession.page;

  try {
    await withTimeout(login(adminPage, 'admin'), 'admin login');
    await addResult('admin auth setup', '/users/login', 'PASS', { currentUrl: adminPage.url() });
  } catch (error) {
    await addResult('admin auth setup', '/users/login', 'FAIL', { error: error.message, currentUrl: adminPage.url() });
  }

  await runFlow('admin sitemap page/button', adminPage, '/admin/sitemap/generator', async ({ page, text }) => {
    const clicked = await clickFirstVisible(page, [
      'form[action*="sitemap/download"] button[type="submit"]',
      'button:has-text("Generate")',
      'input[type="submit"][value*="Generate"]',
    ]);
    await page.waitForLoadState('domcontentloaded', { timeout: 8000 }).catch(() => {});
    await page.waitForTimeout(1500);
    const after = await pageText(page);
    return {
      status: /Sitemap|Generate/i.test(text) && !hasServerError(after) ? 'PASS' : 'PARTIAL',
      details: { clickedSelector: clicked, finalUrl: page.url() },
    };
  });

  await adminSession.context.close();

  await browser.close();
  console.log(JSON.stringify({ baseUrl, generatedAt: new Date().toISOString(), results }, null, 2));
})().catch((error) => {
  console.error(error);
  process.exit(1);
});
