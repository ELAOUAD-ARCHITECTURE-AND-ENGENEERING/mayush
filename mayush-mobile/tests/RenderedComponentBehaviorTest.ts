import React from 'react';
import { renderWithMayushProviders } from './helpers/renderHelper';
import { OrdersListScreen } from '../src/screens/orders/OrdersListScreen';
import { HomeScreen } from '../src/screens/discovery/HomeScreen';
import { SettingsScreen } from '../src/screens/account/SettingsScreen';
import { CartScreen } from '../src/screens/commerce/CartScreen';
import { CheckoutSummaryScreen } from '../src/screens/checkout/CheckoutSummaryScreen';
import { LoginScreen } from '../src/screens/auth/LoginScreen';
import { authState } from '../src/commerce/authState';
import { BuyerOrder } from '../src/commerce/orderState';
import { emptyCartState, CartState } from '../src/commerce/cartState';
import { defaultSavedAddresses } from '../src/commerce/checkoutState';

export async function runRenderedComponentBehaviorTests(assert: (condition: boolean, message: string) => void) {
  // --- 1. ORDERS RENDERED TESTS ---
  const sampleOrders: BuyerOrder[] = [
    {
      orderId: 'MAYUSH-ORD-901',
      checkoutAttemptId: 'att-901',
      createdAtIso: '2026-08-10T10:00:00Z',
      createdAtLabel: '10 août 2026',
      orderStatus: 'shipped',
      paymentStatus: 'confirmed',
      deliveryStatus: 'shipped',
      itemsCount: 1,
      totalMad: 2950,
      currency: 'MAD',
      lines: [
        {
          orderLineId: 'line-1',
          productId: 101,
          productTitle: 'Fauteuil Lounge Luna',
          variantTitle: 'Tissu bouclé · Beige',
          quantity: 1,
          unitPriceMad: 2950,
          lineTotalMad: 2950,
        },
      ],
      shippingAddress: defaultSavedAddresses[0],
      billingAddress: defaultSavedAddresses[0],
      deliveryMethodId: 'express',
      deliveryMethodName: 'Livraison Express',
      paymentMethodId: 'cmi',
      paymentMethodName: 'Carte bancaire marocaine (CMI)',
    },
    {
      orderId: 'MAYUSH-ORD-902',
      checkoutAttemptId: 'att-902',
      createdAtIso: '2026-08-08T10:00:00Z',
      createdAtLabel: '8 août 2026',
      orderStatus: 'delivered',
      paymentStatus: 'confirmed',
      deliveryStatus: 'delivered',
      itemsCount: 1,
      totalMad: 1850,
      currency: 'MAD',
      lines: [
        {
          orderLineId: 'line-2',
          productId: 102,
          productTitle: 'Table Basse Kyoto',
          variantTitle: 'Chêne massif',
          quantity: 1,
          unitPriceMad: 1850,
          lineTotalMad: 1850,
        },
      ],
      shippingAddress: defaultSavedAddresses[0],
      billingAddress: defaultSavedAddresses[0],
      deliveryMethodId: 'standard',
      deliveryMethodName: 'Livraison Standard',
      paymentMethodId: 'cash-on-delivery',
      paymentMethodName: 'Paiement à la livraison',
    },
  ];

  let selectedOrderId = '';
  const ordersRender = renderWithMayushProviders(
    <OrdersListScreen
      orders={sampleOrders}
      onOpenOrder={(id) => { selectedOrderId = id; }}
      onNavigateTab={() => {}}
    />,
    { language: 'fr' }
  );

  assert(ordersRender.getByText('Mes commandes') !== null, 'OrdersListScreen renders title in FR');
  assert(ordersRender.getByText('MAYUSH-ORD-901') !== null, 'OrdersListScreen renders first order card');

  // Test Order card press
  const detailBtn = ordersRender.getByLabel('Voir les détails MAYUSH-ORD-901');
  assert(detailBtn !== null, 'OrdersListScreen renders order detail press button');
  if (detailBtn) {
    ordersRender.press(detailBtn);
    assert(selectedOrderId === 'MAYUSH-ORD-901', 'OrdersListScreen order detail button press dispatches exact order ID');
  }

  // --- 2. HOME RENDERED TESTS ---
  let homePromotionsPressed = false;
  let homeRecentlyViewedPressed = false;
  let homeProductSelected = 0;

  const homeRender = renderWithMayushProviders(
    <HomeScreen
      authState={authState}
      onOpenCategory={() => {}}
      onOpenProduct={(id) => { homeProductSelected = id; }}
      onOpenCart={() => {}}
      onOpenAccount={() => {}}
      onOpenPromotions={() => { homePromotionsPressed = true; }}
      onOpenRecentlyViewed={() => { homeRecentlyViewedPressed = true; }}
    />
  );

  assert(homeRender.getByText('Offres du moment') !== null, 'HomeScreen renders Promotions banner section');
  const promoButton = homeRender.getByLabel('Profiter des offres') || homeRender.getByText('En profiter');
  assert(promoButton !== null, 'HomeScreen renders Promotions CTA button');
  if (promoButton) {
    homeRender.press(promoButton);
    assert(homePromotionsPressed === true, 'HomeScreen Promotions CTA press dispatches callback');
  }

  // --- 3. SETTINGS RENDERED TESTS ---
  let settingsAboutPressed = false;
  const settingsRender = renderWithMayushProviders(
    <SettingsScreen
      onNavigateAboutMayush={() => { settingsAboutPressed = true; }}
      onBack={() => {}}
    />
  );

  assert(settingsRender.getByText('À propos de Mayush Design') !== null, 'SettingsScreen renders About Mayush row');
  const aboutRow = settingsRender.getByText('À propos de Mayush Design');
  if (aboutRow) {
    settingsRender.press(aboutRow);
    assert(settingsAboutPressed === true, 'SettingsScreen About Mayush row press dispatches about-mayush route');
  }

  // --- 4. CART RENDERED TESTS ---
  const sampleCart: CartState = {
    ...emptyCartState(),
    lines: [
      {
        id: 'line-101',
        productId: 101,
        title: 'Fauteuil Lounge Luna',
        variantTitle: 'Tissu bouclé · Beige',
        quantity: 1,
        unitPriceMad: 2950,
        lineTotalMad: 2950,
        imageUri: undefined,
        sellerId: 'seller-1',
        sellerName: 'Atelier Atlas',
      },
    ],
  };

  let cartCheckoutPressed = false;
  const cartRender = renderWithMayushProviders(
    <CartScreen
      cart={sampleCart}
      onUpdateQuantity={() => {}}
      onRemoveLine={() => {}}
      onEditVariant={() => {}}
      onProceedToCheckout={() => { cartCheckoutPressed = true; }}
      onExploreProducts={() => {}}
    />
  );

  assert(cartRender.getByText('Mon panier') !== null, 'CartScreen renders cart header');
  assert(cartRender.getByText('Fauteuil Lounge Luna') !== null, 'CartScreen renders cart line title');

  // --- 5. CHECKOUT SUMMARY RENDERED TESTS ---
  const summaryRender = renderWithMayushProviders(
    <CheckoutSummaryScreen
      cart={sampleCart}
      address={defaultSavedAddresses[0]}
      deliveryMethod="standard"
      paymentMethod="cmi"
      onBack={() => {}}
      onChooseAddress={() => {}}
    />
  );

  assert(summaryRender.getByText('Finaliser ma commande') !== null, 'CheckoutSummaryScreen renders summary interface title');

  // --- 6. AUTH LOGIN RENDERED TESTS ---
  let loginSubmittedUser = '';
  const loginRender = renderWithMayushProviders(
    <LoginScreen
      initialEmailOrPhone="karim@mayush.ma"
      initialPassword="Password123"
      onLoginSubmit={(u) => { loginSubmittedUser = u; }}
      onForgotPassword={() => {}}
      onCreateAccount={() => {}}
      onBack={() => {}}
    />
  );

  assert(loginRender.getByText('Se connecter') !== null, 'LoginScreen renders Login CTA button');
  const loginBtn = loginRender.getByText('Se connecter');
  assert(loginBtn !== null, 'LoginScreen submit button is present');
  if (loginBtn) {
    loginRender.press(loginBtn);
    assert(loginSubmittedUser === 'karim@mayush.ma', 'LoginScreen submit press dispatches login callback with user inputs');
  }
}
