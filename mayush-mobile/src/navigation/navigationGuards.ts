import { ScreenKey, AUTH_PROTECTED_SCREENS } from './screenKeys';

export function isAuthProtectedScreen(screen: ScreenKey): boolean {
  return AUTH_PROTECTED_SCREENS.includes(screen);
}

export function resolveSafeBackDestination(currentScreen: ScreenKey): ScreenKey {
  if (isAuthProtectedScreen(currentScreen)) return 'account';
  if (currentScreen.startsWith('checkout-') || currentScreen.startsWith('payment-')) return 'cart';
  if (currentScreen.startsWith('order-detail-')) return 'orders-list';
  if (currentScreen.startsWith('help-') || currentScreen.startsWith('faq-')) return 'help-center-home';
  return 'home';
}
