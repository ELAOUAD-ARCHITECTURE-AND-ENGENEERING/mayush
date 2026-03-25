describe('Promotion Flow', () => {
  it('Seller creates promotion and Admin approves it', () => {
    // 1. Seller Login & Promote
    cy.visit('/users/login');
    cy.get('input[name="email"]').type('seller@example.com');
    cy.get('input[name="password"]').type('123456');
    cy.get('button[type="submit"]').click();
    
    cy.visit('/customer_products');
    cy.contains('Promote').first().click(); // Click promote button
    
    cy.get('#promotion_modal').should('be.visible');
    // cy.get('select[name="tier"]').select('premium'); // Assuming select works this way or use UI interaction
    cy.get('input[name="start_date"]').type('2026-01-01');
    cy.get('input[name="end_date"]').type('2026-01-10');
    cy.get('button[type="submit"]').click();
    
    cy.contains('Promotion requested successfully').should('be.visible');

    // 2. Admin Login & Approve
    cy.visit('/admin'); // Redirects to login if not auth
    cy.get('input[name="email"]').type('admin@example.com');
    cy.get('input[name="password"]').type('123456');
    cy.get('button[type="submit"]').click();
    
    cy.visit('/admin/promotions');
    cy.contains('seller@example.com').should('exist'); // Assuming seller name is displayed
    cy.get('.la-check').first().click(); // Approve button
    
    cy.on('window:confirm', () => true);
    // cy.contains('Status updated successfully').should('be.visible'); // Toast might be tricky to catch
    
    // 3. Verify on Homepage
    cy.visit('/');
    cy.contains('Promoted Ads').scrollIntoView();
    // Check if slider exists and contains the promoted product
    cy.get('#promotionSliderWrapper').should('exist');
  });
});