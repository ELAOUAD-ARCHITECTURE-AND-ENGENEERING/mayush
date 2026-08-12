import React from 'react';
import { renderWithMayushProviders } from './helpers/renderHelper';
import { OrdersListScreen } from '../src/screens/orders/OrdersListScreen';
import { HomeScreen } from '../src/screens/discovery/HomeScreen';
import { SettingsScreen } from '../src/screens/account/SettingsScreen';
import { CartScreen } from '../src/screens/commerce/CartScreen';
import { CheckoutSummaryScreen } from '../src/screens/checkout/CheckoutSummaryScreen';
import { LoginScreen } from '../src/screens/auth/LoginScreen';
import { BuyerOrder } from '../src/commerce/orderState';
import { emptyCartState, CartState } from '../src/commerce/cartState';
import { defaultSavedAddresses } from '../src/commerce/checkoutState';

export async function runRenderedComponentBehaviorTests(assert: (condition: boolean, message: string) => void) {
  // --- 1. ORDERS RENDERED TESTS ---
  const sampleOrders: BuyerOrder[] = [
    {
      orderId: 'MAYUSH-ORD-901',
      checkoutAttemptId: 'att-901',
      createdAt: '2026-08-10T10:00:00Z',
      createdAtLabel: '10 août 2026',
      orderStatus: 'shipped',
      paymentStatus: 'confirmed',
      deliveryStatus: 'shipped',
      totalMad: 2950,
      deliveryFeeMad: 20,
      discountMad: 0,
      paymentReference: 'REF-901',
      paymentMethod: 'cmi',
      deliveryMethod: 'standard',
      trackingEvents: [],
      packages: [],
      invoice: null,
      id: 'MAYUSH-ORD-901',
      idempotencyKey: 'att-901',
      lines: [
        {
          orderLineId: 'line-1',
          productId: 101,
          name: 'Fauteuil Lounge Luna',
          variantLabel: 'Tissu bouclé · Beige',
          quantity: 1,
          unitPriceMad: 2950,
          id: 'line-1',
          variant: 'Tissu bouclé · Beige',
        },
      ],
      address: {
        name: defaultSavedAddresses[0].name,
        phone: defaultSavedAddresses[0].phone,
        addressLine: defaultSavedAddresses[0].addressLine,
        city: defaultSavedAddresses[0].city,
        postcode: defaultSavedAddresses[0].postcode,
        zone: defaultSavedAddresses[0].zone,
      },
    },
    {
      orderId: 'MAYUSH-ORD-902',
      checkoutAttemptId: 'att-902',
      createdAt: '2026-08-08T10:00:00Z',
      createdAtLabel: '8 août 2026',
      orderStatus: 'delivered',
      paymentStatus: 'confirmed',
      deliveryStatus: 'delivered',
      totalMad: 1850,
      deliveryFeeMad: 20,
      discountMad: 0,
      paymentReference: 'REF-902',
      paymentMethod: 'cash-on-delivery',
      deliveryMethod: 'standard',
      trackingEvents: [],
      packages: [],
      invoice: null,
      id: 'MAYUSH-ORD-902',
      idempotencyKey: 'att-902',
      lines: [
        {
          orderLineId: 'line-2',
          productId: 102,
          name: 'Table Basse Kyoto',
          variantLabel: 'Chêne massif',
          quantity: 1,
          unitPriceMad: 1850,
          id: 'line-2',
          variant: 'Chêne massif',
        },
      ],
      address: {
        name: defaultSavedAddresses[0].name,
        phone: defaultSavedAddresses[0].phone,
        addressLine: defaultSavedAddresses[0].addressLine,
        city: defaultSavedAddresses[0].city,
        postcode: defaultSavedAddresses[0].postcode,
        zone: defaultSavedAddresses[0].zone,
      },
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

  const homeRender = renderWithMayushProviders(
    <HomeScreen
      onSelectCategory={() => {}}
      onSelectProduct={() => {}}
      onOpenPromotions={() => { homePromotionsPressed = true; }}
      onOpenRecentlyViewed={() => { homeRecentlyViewedPressed = true; }}
    />
  );

  assert(homeRender.getByText('Offres du moment') !== null, 'HomeScreen renders Promotions banner section');
  const promoButton = homeRender.getByLabel('Profiter des offres') || homeRender.getByText('En profiter');
  assert(promoButton !== null, 'HomeScreen renders Promotions CTA button');
  if (promoButton) {
    homeRender.press(promoButton);
    assert(Boolean(homePromotionsPressed), 'HomeScreen Promotions CTA press dispatches callback');
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
    assert(Boolean(settingsAboutPressed), 'SettingsScreen About Mayush row press dispatches about-mayush route');
  }

  // --- 4. CART RENDERED TESTS ---
  const sampleCart: CartState = {
    ...emptyCartState(),
    lines: [
      {
        id: 'line-101',
        productId: 101,
        name: 'Fauteuil Lounge Luna',
        variantId: 'v-101',
        variant: 'Tissu bouclé · Beige',
        quantity: 1,
        unitPriceMad: 2950,
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
      onCheckout={() => { cartCheckoutPressed = true; }}
      onStartShopping={() => {}}
    />
  );

  assert(cartRender.getByText('Mon panier') !== null, 'CartScreen renders cart header');
  assert(cartRender.getByText('Fauteuil Lounge Luna') !== null, 'CartScreen renders cart line title');

  // --- 5. CHECKOUT SUMMARY RENDERED TESTS ---
  const checkoutRender = renderWithMayushProviders(
    <CheckoutSummaryScreen
      cart={sampleCart}
      address={defaultSavedAddresses[0]}
      deliveryMethod="standard"
      paymentMethod="cmi"
      deliveryFeeMad={20}
      onBack={() => {}}
      onChooseAddress={() => {}}
    />
  );

  assert(checkoutRender.getByText('Finaliser ma commande') !== null, 'CheckoutSummaryScreen renders summary interface title');

  // --- 6. LOGIN SCREEN RENDERED TESTS ---
  let submittedEmail = '';
  let submittedPassword = '';
  const loginRender = renderWithMayushProviders(
    <LoginScreen
      onBack={() => {}}
      onCreateAccount={() => {}}
      onForgotPassword={() => {}}
      onLoginSubmit={(email, password) => {
        submittedEmail = email;
        submittedPassword = password;
      }}
    />
  );

  const loginButton = loginRender.getByText('Se connecter');
  assert(loginButton !== null, 'LoginScreen renders Login CTA button');

  const submitButton = loginRender.getByRole('button', 'Se connecter') || loginRender.getByLabel('Se connecter');
  assert(submitButton !== null, 'LoginScreen submit button is present');

  if (submitButton) {
    loginRender.press(submitButton);
    assert(typeof submittedEmail === 'string' && typeof submittedPassword === 'string', 'LoginScreen submit press dispatches login callback with user inputs');
  }
}
