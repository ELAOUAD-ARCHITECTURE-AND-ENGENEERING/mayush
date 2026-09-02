import * as Linking from 'expo-linking';

import type { ScreenKey } from './screenKeys';
import { resolveScreen } from './resolveScreen';

const ALLOWED_HOSTS = ['mayushdesign.com', 'www.mayushdesign.com'];
const ALLOWED_SCHEMES = ['https:', 'mayush:', 'exp:'];

const isValidDeepLink = (url: string): boolean => {
  try {
    // App-scheme deep links (mayush://path) are always valid
    if (url.startsWith('mayush://') || url.startsWith('exp://')) return true;
    const parsed = new URL(url);
    return ALLOWED_SCHEMES.includes(parsed.protocol) &&
           ALLOWED_HOSTS.includes(parsed.hostname);
  } catch {
    return false;
  }
};

export interface RootDeepLinkTarget {
  screen: ScreenKey;
  inspirationSlug?: string;
  inspirationBackScreen?: ScreenKey;
}

interface RootDeepLinkSubscriptionOptions {
  getCurrentScreen: () => ScreenKey;
  onTarget: (target: RootDeepLinkTarget) => void;
  onInitialUrlError?: (error: unknown) => void;
}

export const resolveRootDeepLinkTarget = (
  url: string,
  currentScreen: ScreenKey,
  isColdStart: boolean,
): RootDeepLinkTarget => {
  const resolution = resolveScreen(url);
  if (!resolution.key) return { screen: 'home' };

  if (resolution.key === 'inspiration-detail') {
    return {
      screen: resolution.key,
      inspirationSlug: resolution.params?.slug ?? '',
      inspirationBackScreen: isColdStart ? 'home' : currentScreen,
    };
  }

  if (resolution.key === 'inspirations-list') {
    return {
      screen: resolution.key,
      inspirationBackScreen: isColdStart ? 'home' : currentScreen,
    };
  }

  return { screen: resolution.key };
};

export const subscribeToRootDeepLinks = ({
  getCurrentScreen,
  onTarget,
  onInitialUrlError,
}: RootDeepLinkSubscriptionOptions): (() => void) => {
  let active = true;

  const dispatch = (url: string, isColdStart: boolean) => {
    if (!active || !isValidDeepLink(url)) return;
    onTarget(resolveRootDeepLinkTarget(url, getCurrentScreen(), isColdStart));
  };

  const subscription = Linking.addEventListener('url', ({ url }) => dispatch(url, false));

  void Linking.getInitialURL()
    .then((url) => {
      if (url) dispatch(url, true);
    })
    .catch((error: unknown) => {
      if (active) onInitialUrlError?.(error);
    });

  return () => {
    active = false;
    subscription.remove();
  };
};
