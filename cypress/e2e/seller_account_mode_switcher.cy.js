const credentials = {
  sellerEmail: Cypress.env('SELLER_EMAIL') || 'seller@example.com',
  sellerPassword: Cypress.env('SELLER_PASSWORD') || '123456',
  buyerEmail: Cypress.env('BUYER_EMAIL') || 'customer@example.com',
  buyerPassword: Cypress.env('BUYER_PASSWORD') || '123456',
};

function login(email, password) {
  cy.visit('/users/login');
  cy.get('input[name="email"]').clear().type(email);
  cy.get('input[name="password"]').clear().type(password, { log: false });
  cy.get('button[type="submit"]').click();
}

describe('Seller account mode switcher', () => {
  beforeEach(() => {
    cy.clearCookies();
  });

  it('lets a verified seller switch between seller and buyer modes without ending the session', () => {
    login(credentials.sellerEmail, credentials.sellerPassword);

    cy.visit('/seller/dashboard');
    cy.get('[data-account-mode-switcher]').contains('Switch to Buyer').click();
    cy.location('pathname').should('eq', '/dashboard');
    cy.get('[data-account-mode-switcher]').contains('Switch to Seller');

    cy.visit('/purchase_history');
    cy.location('pathname').should('eq', '/purchase_history');

    cy.visit('/seller/dashboard');
    cy.location('pathname').should('eq', '/dashboard');
    cy.get('[data-account-mode-switcher]').contains('Switch to Seller').click();
    cy.location('pathname').should('eq', '/seller/dashboard');
    cy.get('[data-account-mode-switcher]').contains('Switch to Buyer');
  });

  it('does not render or allow the switcher for buyer-only accounts', () => {
    login(credentials.buyerEmail, credentials.buyerPassword);

    cy.visit('/dashboard');
    cy.get('[data-account-mode-switcher]').should('not.exist');
    cy.contains('Switch to Seller').should('not.exist');
    cy.contains('Switch to Buyer').should('not.exist');

    cy.get('meta[name="csrf-token"]').invoke('attr', 'content').then((token) => {
      cy.request({
        method: 'POST',
        url: '/account-mode/switch',
        headers: { 'X-CSRF-TOKEN': token },
        body: { mode: 'seller' },
        failOnStatusCode: false,
      }).its('status').should('eq', 403);
    });

    cy.request({
      url: '/seller/dashboard',
      failOnStatusCode: false,
    }).its('status').should('eq', 404);
  });
});
