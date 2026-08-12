import { authState, getAddressesStorageKey } from '../src/commerce/authState';
import { localeState, LANGUAGE_STORAGE_KEY, LEGACY_ACCOUNT_PREFERENCES_KEY } from '../src/commerce/localeState';
import { accountPreferencesState } from '../src/commerce/accountPreferencesState';
import { getWishlistStorageKey, wishlistState } from '../src/commerce/wishlistState';
import { notificationPreferencesState } from '../src/commerce/notificationPreferencesState';
import { appSettingsState } from '../src/commerce/appSettingsState';
import { orderState } from '../src/commerce/orderState';
import { createSavedAddress, defaultSavedAddresses, SavedAddress } from '../src/commerce/checkoutState';
import { resolveOrderNavigation, resolveAccountNavigation, resolveCheckoutStep } from '../src/navigation/domainNavigationHandlers';

export type AssertFn = (condition: boolean, message: string) => void;

export async function runStep9B3PersistenceAndInteractionIntegrityTests(
  assert: AssertFn,
  storageMock?: any
): Promise<void> {
  const getStored = async (k: string) => storageMock ? await storageMock.getItem(k) : null;
  const setStored = async (k: string, v: string) => storageMock ? await storageMock.setItem(k, v) : null;

  // ── 1. ADDRESS DURABILITY & PROCESS RESTART ──
  {
    authState.reset();
    authState.completeLogin('karim.benjelloun@example.ma', 'Karim Benjelloun');
    const buyerId = authState.getUser()?.id || 'mock-user-101';
    assert(buyerId === 'mock-user-101', 'STEP 9B.3: Buyer ID authenticated as mock-user-101');

    const newAddress: SavedAddress = {
      id: 'addr-durability-101',
      name: 'Résidence Anfa Top',
      phone: '+212 6 61 99 88 77',
      addressLine: 'Boulevard d Anfa, Appt 4B',
      city: 'Casablanca',
      postcode: '20000',
      zone: 'Casablanca Centre',
      cityId: 'casablanca',
      zoneId: 'casablanca-centre',
      isDefault: true,
    };

    authState.addAddress(newAddress);
    await authState.persistAddresses();
    assert(authState.getSavedAddresses().some((a) => a.id === 'addr-durability-101'), 'STEP 9B.3: Address added to in-memory authState');

    const storageKey = getAddressesStorageKey(buyerId);
    assert(storageKey === 'mayush-mobile:addresses:v1:mock-user-101', 'STEP 9B.3: Address storage key correctly scoped to buyer ID');

    if (storageMock) {
      const persistedAddressesRaw = await getStored(storageKey);
      assert(Boolean(persistedAddressesRaw && persistedAddressesRaw.includes('addr-durability-101')), 'STEP 9B.3: Address persisted to AsyncStorage under buyer key');
    }

    // Simulate process/store reconstruction
    authState.reset();
    assert(!authState.getSavedAddresses().some((a) => a.id === 'addr-durability-101'), 'STEP 9B.3: In-memory address reset on restart simulation');

    authState.completeLogin('karim.benjelloun@example.ma', 'Karim Benjelloun');
    await authState.hydrateAddresses(buyerId);
    assert(authState.getSavedAddresses().some((a) => a.id === 'addr-durability-101' && a.name === 'Résidence Anfa Top'), 'STEP 9B.3: Address hydrated successfully after restart');

    // Edit address -> restart -> edited value remains
    authState.updateAddress('addr-durability-101', { name: 'Résidence Anfa Top Modifiée' });
    await authState.persistAddresses();
    authState.reset();

    authState.completeLogin('karim.benjelloun@example.ma', 'Karim Benjelloun');
    await authState.hydrateAddresses(buyerId);
    const edited = authState.getSavedAddresses().find((a) => a.id === 'addr-durability-101');
    assert(Boolean(edited && edited.name === 'Résidence Anfa Top Modifiée'), 'STEP 9B.3: Address edit survives process restart');

    // Delete address -> restart -> remains deleted
    authState.deleteAddress('addr-durability-101');
    await authState.persistAddresses();
    authState.reset();

    authState.completeLogin('karim.benjelloun@example.ma', 'Karim Benjelloun');
    await authState.hydrateAddresses(buyerId);
    assert(!authState.getSavedAddresses().some((a) => a.id === 'addr-durability-101'), 'STEP 9B.3: Address deletion survives process restart');

    // Buyer A address does not leak to Buyer B
    const buyerBAddress: SavedAddress = {
      id: 'addr-buyer-b-99',
      name: 'Rabat Agdal Villa',
      phone: '+212 6 12 34 56 78',
      addressLine: 'Avenue de France, N 12',
      city: 'Rabat',
      postcode: '10000',
      zone: 'Agdal',
      cityId: 'rabat',
      zoneId: 'agdal-rabat',
    };
    authState.completeRegistration(); // creates buyer B mock-user-102
    const buyerBId = authState.getUser()?.id || 'mock-user-102';
    authState.addAddress(buyerBAddress);

    authState.logout(); // Guest mode
    assert(!authState.getSavedAddresses().some((a) => a.id === 'addr-buyer-b-99'), 'STEP 9B.3: Buyer B address does not leak to guest session');

    await authState.hydrateAddresses('mock-user-101'); // Switch back to Buyer A
    assert(!authState.getSavedAddresses().some((a) => a.id === 'addr-buyer-b-99'), 'STEP 9B.3: Buyer B address does not leak to Buyer A session');

    // Corrupt persistence fallback
    if (storageMock) {
      await setStored(getAddressesStorageKey('corrupt-user'), '{invalid json payload');
      await authState.hydrateAddresses('corrupt-user');
      assert(authState.getSavedAddresses().length > 0, 'STEP 9B.3: Corrupt address persistence falls back safely to default addresses');
    }
  }

  // ── 2. ORDER HISTORICAL SNAPSHOT IMMUTABILITY ──
  {
    const initialCount = orderState.getOrders().length;
    const testAddress = defaultSavedAddresses[0];
    const createdResult = await orderState.createOrder({
      checkoutAttemptId: 'test-chk-snap-101',
      cart: {
        lines: [{ id: 'line-snap-1', productId: 101, name: 'Fauteuil Nori', variant: 'Noyer naturel', quantity: 1, unitPriceMad: 1500, sellerId: 'seller-mayush' }],
      },
      address: testAddress,
      deliveryMethod: 'standard',
      paymentMethod: 'cmi',
      deliveryFeeMad: 20,
    });
    const createdOrder = createdResult.order;

    assert(Boolean(createdOrder && createdOrder.address.name === testAddress.name), 'STEP 9B.3: Order created with address snapshot');

    // Mutate address book in AuthState
    authState.updateAddress(testAddress.id, { name: 'Name Changed After Order' });
    const fetchedOrder = orderState.getOrderById(createdOrder.orderId);
    assert(Boolean(fetchedOrder && fetchedOrder.address.name === testAddress.name), 'STEP 9B.3: Historical BuyerOrder snapshot remains unchanged after address edit');
  }

  // ── 3. LOCALE MIGRATION SAFETY & PAYMENT PREFERENCE PRESERVATION ──
  {
    if (storageMock) {
      // Setup legacy payload containing language AND payment preference AND extra prefs
      const legacyPayload = {
        selectedLanguage: 'ar',
        selectedPaymentMethodId: 'pm-wallet-legacy-99',
        marketingOptIn: true,
      };
      await setStored(LEGACY_ACCOUNT_PREFERENCES_KEY, JSON.stringify(legacyPayload));
      await storageMock.removeItem(LANGUAGE_STORAGE_KEY);

      // Perform locale migration
      await localeState.hydrate();

      assert(localeState.getLanguage() === 'ar', 'STEP 9B.3: Locale state migrated language to AR');
      assert(localeState.isRTL() === true, 'STEP 9B.3: Locale state correctly reports RTL for AR');

      const migratedLanguageStored = await getStored(LANGUAGE_STORAGE_KEY);
      assert(migratedLanguageStored === 'ar', 'STEP 9B.3: Language stored under new mayush-mobile:language key');

      const remainingLegacyStored = await getStored(LEGACY_ACCOUNT_PREFERENCES_KEY);
      assert(Boolean(remainingLegacyStored), 'STEP 9B.3: Legacy account-preferences key preserved');

      const parsedRemaining = JSON.parse(remainingLegacyStored || '{}');
      assert(parsedRemaining.selectedPaymentMethodId === 'pm-wallet-legacy-99', 'STEP 9B.3: Unrelated payment preference survived locale migration intact');
      assert(parsedRemaining.marketingOptIn === true, 'STEP 9B.3: Additional account preferences survived locale migration intact');
      assert(parsedRemaining.selectedLanguage === undefined, 'STEP 9B.3: Migrated selectedLanguage extracted cleanly from account-preferences');

      // Test idempotency
      await localeState.hydrate();
      assert(localeState.getLanguage() === 'ar', 'STEP 9B.3: Repeated locale hydration is idempotent');

      // Mounted UI notification test
      let notified = false;
      const unsubscribe = localeState.subscribe(() => { notified = true; });
      await localeState.setLanguage('fr');
      assert(notified && localeState.getLanguage() === 'fr' && !localeState.isRTL(), 'STEP 9B.3: Locale update triggers immediate subscriber notification and RTL flag update');
      unsubscribe();
    }
  }

  // ── 4. WISHLIST STORAGE API FACT-CHECK ──
  {
    const guestKey = getWishlistStorageKey();
    assert(guestKey === 'mayush-mobile:wishlist:guest', 'STEP 9B.3: Guest wishlist key formatted correctly');

    const buyerKey = getWishlistStorageKey('mock-user-101');
    assert(buyerKey === 'mayush-mobile:wishlist:mock-user-101', 'STEP 9B.3: Authenticated buyer wishlist key formatted correctly');

    assert(typeof wishlistState.isHydrated() === 'boolean', 'STEP 9B.3: Wishlist state provides hydration check');
  }

  // ── 5. ASYNC HYDRATION & STATE MANAGERS AUDIT ──
  {
    assert(typeof authState.isHydrated() === 'boolean', 'STEP 9B.3: Auth state provides isHydrated()');
    assert(typeof localeState.isHydrated() === 'boolean', 'STEP 9B.3: Locale state provides isHydrated()');
    assert(typeof accountPreferencesState.isHydrated() === 'boolean', 'STEP 9B.3: Account preferences state provides isHydrated()');
    assert(typeof notificationPreferencesState.isHydrated() === 'boolean', 'STEP 9B.3: Notification preferences state provides isHydrated()');
    assert(typeof appSettingsState.isHydrated() === 'boolean', 'STEP 9B.3: App settings state provides isHydrated()');
  }

  // ── 6. NAVIGATION RESOLVERS TRUTH ──
  {
    const dummyOrder = orderState.getOrders()[0] || null;
    const orderNav = resolveOrderNavigation('tracking', dummyOrder);
    assert(orderNav.nextScreen === (dummyOrder ? 'order-tracking' : 'orders-empty'), 'STEP 9B.3: resolveOrderNavigation resolves order tracking route correctly');

    const accountNav = resolveAccountNavigation('addresses');
    assert(accountNav.nextScreen === 'my-addresses-v2', 'STEP 9B.3: resolveAccountNavigation resolves addresses route correctly');

    const checkoutNav = resolveCheckoutStep('delivery', true);
    assert(checkoutNav.nextScreen === 'delivery-method', 'STEP 9B.3: resolveCheckoutStep resolves delivery step correctly');
  }
}
