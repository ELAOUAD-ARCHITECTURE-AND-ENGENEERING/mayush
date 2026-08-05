/**
 * Mayush Design System & Phase 5 Test Suite Runner
 */

const fs = require('fs');
const path = require('path');

console.log('==================================================');
console.log('RUNNING MAYUSH DESIGN SYSTEM & PHASE 5 TEST SUITE');
console.log('==================================================');

let errors = 0;
let passes = 0;

function assert(condition, message) {
  if (!condition) {
    console.error(`[FAIL] ${message}`);
    errors++;
  } else {
    console.log(`[PASS] ${message}`);
    passes++;
  }
}

try {
  // 1. Audit Brand Color Tokens
  const colorsFile = fs.readFileSync(path.join(__dirname, '../src/design-system/tokens/colors.ts'), 'utf8');
  assert(colorsFile.includes("orange500: '#FF7900'"), 'brand/orange/500 token is #FF7900');
  assert(colorsFile.includes("navy900: '#101D35'"), 'brand/navy/900 token is #101D35');
  assert(colorsFile.includes("cream: '#FFF9F1'"), 'surface/cream token is #FFF9F1');
  assert(colorsFile.includes("borderWarm: '#EEE7DE'"), 'surface/borderWarm token is #EEE7DE');

  // 2. Audit Typography Tokens
  const typographyFile = fs.readFileSync(path.join(__dirname, '../src/design-system/tokens/typography.ts'), 'utf8');
  assert(typographyFile.includes('display: 30'), 'fontSizes.display is 30px');
  assert(typographyFile.includes('xxl: 24'), 'fontSizes.xxl (pageTitle) is 24px');
  assert(typographyFile.includes('xl: 20'), 'fontSizes.xl (sectionTitle) is 20px');

  // 3. Audit Radii & Sizing
  const radiiFile = fs.readFileSync(path.join(__dirname, '../src/design-system/tokens/radii.ts'), 'utf8');
  assert(radiiFile.includes('lg: 12'), 'Primary button border radius token (lg) is 12px');
  assert(radiiFile.includes('xl: 16'), 'Card border radius token (xl) is 16px');

  const sizingFile = fs.readFileSync(path.join(__dirname, '../src/design-system/tokens/sizing.ts'), 'utf8');
  assert(sizingFile.includes('buttonHeight: 48'), 'Button height token is 48px');
  assert(sizingFile.includes('inputHeight: 48'), 'Input height token is 48px');

  // 4. Audit Theme File (French LTR vs Arabic RTL)
  const themeFile = fs.readFileSync(path.join(__dirname, '../src/design-system/theme/theme.ts'), 'utf8');
  assert(themeFile.includes("const isRTL = language === 'ar'"), 'Theme creates LTR for fr and RTL for ar');

  // 5. Audit Asset Resolution
  assert(fs.existsSync(path.join(__dirname, '../assets/brand/logo-transparent.png')), 'Transparent shared brand logo asset exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/home-hero-scene.png')), 'Reference-matched Home hero artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/home-hero-premium-scene.png')), 'Second Home hero slide artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/home-hero-category-scene.png')), 'Third Home hero slide artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/onboarding-step-1-scene.png')), 'French onboarding step 1 artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/onboarding-step-2-scene.png')), 'French onboarding step 2 artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/onboarding-step-3-scene.png')), 'French onboarding step 3 artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/onboarding-step-1-scene-ar.png')), 'Arabic onboarding step 1 artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/onboarding-step-2-scene-ar.png')), 'Arabic onboarding step 2 artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/onboarding-step-3-scene-ar.png')), 'Arabic onboarding step 3 artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/icon.png')), 'Official brand app icon derivative exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/splash-icon.png')), 'Official brand splash icon derivative exists');

  // 6. Audit Exported Components (MayushIcon adds a shared icon primitive)
  const componentsDir = path.join(__dirname, '../src/design-system/components');
  let componentFiles = [];
  const subdirs = ['actions', 'brand', 'commerce', 'feedback', 'forms', 'layout', 'navigation', 'typography'];

  subdirs.forEach((sub) => {
    const dirPath = path.join(componentsDir, sub);
    if (fs.existsSync(dirPath)) {
      const files = fs.readdirSync(dirPath).filter((f) => f.endsWith('.tsx'));
      componentFiles.push(...files);
    }
  });

  assert(componentFiles.length === 21, `Component files count is 21 (Found: ${componentFiles.length})`);

  // 7. Audit Phase 5 Screens (initial entry flow plus discovery/product states)
  assert(fs.existsSync(path.join(__dirname, '../src/screens/entry/SplashScreen.tsx')), 'SCR-ENT-001 SplashScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/entry/LanguageSelectionScreen.tsx')), 'SCR-ENT-002 LanguageSelectionScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/entry/PreparingExperienceScreen.tsx')), 'SCR-ENT-003 PreparingExperienceScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/entry/OnboardingScreen.tsx')), 'SCR-ENT-004 OnboardingScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/commerce/WishlistScreen.tsx')), 'SCR-COM-001 WishlistScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/commerce/CartScreen.tsx')), 'SCR-COM-002 CartScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/commerce/AccountScreen.tsx')), 'SCR-COM-003 AccountScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/discovery/HomeScreen.tsx')), 'SCR-DIS-001 HomeScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/discovery/CategoriesScreen.tsx')), 'SCR-DIS-002 CategoriesScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/discovery/CategoryProductListScreen.tsx')), 'SCR-DIS-003 CategoryProductListScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/product/ProductDetailsScreen.tsx')), 'SCR-PRD-001 ProductDetailsScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/product/VariantSelectorSheet.tsx')), 'SCR-PRD-002 VariantSelectorSheet exists');

  // 8. Audit API Repositories & Client
  assert(fs.existsSync(path.join(__dirname, '../src/services/api/apiClient.ts')), 'apiClient HTTP service exists');
  assert(fs.existsSync(path.join(__dirname, '../src/services/api/catalogService.ts')), 'catalogService API repository exists');

  // 9. Audit Home carousel and configured store currency usage
  const currencyFile = fs.readFileSync(path.join(__dirname, '../src/config/currency.ts'), 'utf8');
  const homeScreenContent = fs.readFileSync(path.join(__dirname, '../src/screens/discovery/HomeScreen.tsx'), 'utf8');
  const productDetailsContent = fs.readFileSync(path.join(__dirname, '../src/screens/product/ProductDetailsScreen.tsx'), 'utf8');
  assert(currencyFile.includes("STORE_CURRENCY_CODE = 'MAD'"), 'Mobile store currency matches the configured MAD currency');
  assert(homeScreenContent.includes('pagingEnabled') && homeScreenContent.includes('onMomentumScrollEnd') && homeScreenContent.includes('setInterval'), 'Home hero supports swipe, dot selection, and timed advance');
  assert(!homeScreenContent.includes('\u20ac') && !productDetailsContent.includes('\u20ac'), 'Home and product fallback prices contain no euro currency');

  // 10. Audit Variant Pricing Endpoint Mapping
  const catalogServiceContent = fs.readFileSync(path.join(__dirname, '../src/services/api/catalogService.ts'), 'utf8');
  assert(catalogServiceContent.includes('/api/v2/products/variant/price'), 'Server-authoritative variant price endpoint mapped');

  // 11. Audit Root Navigation Wiring
  assert(fs.existsSync(path.join(__dirname, '../src/navigation/RootNavigator.tsx')), 'RootNavigator exists');
  const appContent = fs.readFileSync(path.join(__dirname, '../App.tsx'), 'utf8');
  assert(appContent.includes('<RootNavigator />'), 'App.tsx renders RootNavigator');

} catch (err) {
  console.error('[CRITICAL FAILURE]', err);
  errors++;
}

console.log('==================================================');
console.log(`TEST SUMMARY: ${passes} PASSED, ${errors} FAILED`);
console.log('==================================================');

if (errors > 0) {
  process.exit(1);
} else {
  process.exit(0);
}
