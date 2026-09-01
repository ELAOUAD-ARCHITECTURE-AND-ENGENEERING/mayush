import { ScreenKey } from './screenKeys';

export interface NavigationState {
  currentScreen: ScreenKey;
  previousScreen?: ScreenKey;
}

export interface NavigationProps {
  currentScreen: ScreenKey;
  onNavigate: (screen: ScreenKey) => void;
  onBack: () => void;
}

export interface AuthReturnDestination {
  route: ScreenKey;
  pendingAction?: 'checkout' | 'favorite' | 'order-tracking' | 'support-ticket';
  favoriteItemId?: string;
}
