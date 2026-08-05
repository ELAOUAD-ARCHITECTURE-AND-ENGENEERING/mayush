import AsyncStorage from '@react-native-async-storage/async-storage';
import React, { useEffect, useState } from 'react';
import { View, StyleSheet } from 'react-native';
import { ThemeProvider } from '../design-system/theme/ThemeProvider';
import { SplashScreen } from '../screens/entry/SplashScreen';
import { LanguageSelectionScreen } from '../screens/entry/LanguageSelectionScreen';
import { PreparingExperienceScreen } from '../screens/entry/PreparingExperienceScreen';
import { OnboardingScreen } from '../screens/entry/OnboardingScreen';
import { WishlistScreen } from '../screens/commerce/WishlistScreen';
import { CartScreen } from '../screens/commerce/CartScreen';
import { AccountScreen } from '../screens/commerce/AccountScreen';
import { HomeScreen } from '../screens/discovery/HomeScreen';
import { CategoriesScreen } from '../screens/discovery/CategoriesScreen';
import { CategoryProductListScreen } from '../screens/discovery/CategoryProductListScreen';
import { ProductDetailsScreen } from '../screens/product/ProductDetailsScreen';
import { VariantSelectorSheet } from '../screens/product/VariantSelectorSheet';
import { CategoryDto, MvpAppLanguage, ProductMiniDto, ProductDetailDto } from '../contracts/api/dto';
import { TabKey } from '../design-system/components/navigation/BottomTabBar';

export type ScreenKey = 'splash' | 'language' | 'preparing' | 'onboarding-1' | 'onboarding-2' | 'onboarding-3' | 'home' | 'categories' | 'wishlist' | 'cart' | 'account' | 'category-products' | 'product-details';

const ONBOARDING_COMPLETE_KEY = 'mayush-mobile:onboarding-complete';
const LANGUAGE_KEY = 'mayush-mobile:language';

interface RootNavigatorContentProps {
  hasCompletedOnboarding: boolean | null;
  onLanguageSelected: (language: MvpAppLanguage) => void;
  onOnboardingCompleted: () => void;
}

export const RootNavigatorContent: React.FC<RootNavigatorContentProps> = ({
  hasCompletedOnboarding,
  onLanguageSelected,
  onOnboardingCompleted,
}) => {
  const [currentScreen, setCurrentScreen] = useState<ScreenKey>('splash');
  const [splashFinished, setSplashFinished] = useState(false);
  const [selectedCategory, setSelectedCategory] = useState<CategoryDto>();
  const [selectedProduct, setSelectedProduct] = useState<ProductMiniDto>();
  const [variantProduct, setVariantProduct] = useState<ProductDetailDto | null>(null);
  const [variantSheetVisible, setVariantSheetVisible] = useState(false);

  const selectCategory = (category: CategoryDto) => { setSelectedCategory(category); setCurrentScreen('category-products'); };
  const selectProduct = (product: ProductMiniDto) => { setSelectedProduct(product); setCurrentScreen('product-details'); };
  const activeTab: TabKey = currentScreen === 'categories' || currentScreen === 'category-products'
    ? 'categories'
    : currentScreen === 'wishlist'
      ? 'wishlist'
      : currentScreen === 'cart'
        ? 'cart'
        : currentScreen === 'account'
          ? 'account'
          : 'home';

  useEffect(() => {
    if (!splashFinished || hasCompletedOnboarding === null) return;
    setCurrentScreen(hasCompletedOnboarding ? 'home' : 'language');
  }, [hasCompletedOnboarding, splashFinished]);

  const navigateTab = (tab: TabKey) => {
    const destinations: Record<TabKey, ScreenKey> = {
      home: 'home',
      categories: 'categories',
      wishlist: 'wishlist',
      cart: 'cart',
      account: 'account',
    };
    setCurrentScreen(destinations[tab]);
  };

  return (
    <View style={styles.container}>
      {currentScreen === 'splash' ? <SplashScreen onFinish={() => setSplashFinished(true)} /> : null}
      {currentScreen === 'language' ? <LanguageSelectionScreen onContinue={(language) => { onLanguageSelected(language); setCurrentScreen('preparing'); }} /> : null}
      {currentScreen === 'preparing' ? <PreparingExperienceScreen onFinish={() => setCurrentScreen('onboarding-1')} /> : null}
      {currentScreen === 'onboarding-1' ? <OnboardingScreen step={1} onNext={() => setCurrentScreen('onboarding-2')} onSkip={onOnboardingCompleted} /> : null}
      {currentScreen === 'onboarding-2' ? <OnboardingScreen step={2} onNext={() => setCurrentScreen('onboarding-3')} onSkip={onOnboardingCompleted} /> : null}
      {currentScreen === 'onboarding-3' ? <OnboardingScreen step={3} onNext={onOnboardingCompleted} onSkip={onOnboardingCompleted} /> : null}
      {currentScreen === 'home' ? <HomeScreen activeTab={activeTab} onSelectCategory={selectCategory} onSelectProduct={selectProduct} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'categories' ? <CategoriesScreen activeTab={activeTab} onSelectCategory={selectCategory} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'wishlist' ? <WishlistScreen onNavigateTab={navigateTab} onBrowseCollections={() => setCurrentScreen('categories')} /> : null}
      {currentScreen === 'cart' ? <CartScreen onNavigateTab={navigateTab} onStartShopping={() => setCurrentScreen('home')} onViewWishlist={() => setCurrentScreen('wishlist')} /> : null}
      {currentScreen === 'account' ? <AccountScreen onNavigateTab={navigateTab} onExplore={() => setCurrentScreen('home')} /> : null}
      {currentScreen === 'category-products' ? <CategoryProductListScreen activeTab={activeTab} category={selectedCategory} onBack={() => setCurrentScreen('categories')} onSelectProduct={selectProduct} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'product-details' ? <ProductDetailsScreen activeTab={activeTab} productId={selectedProduct?.id || 101} initialProduct={selectedProduct} onBack={() => setCurrentScreen('home')} onOpenVariantSheet={(product) => { setVariantProduct(product); setVariantSheetVisible(true); }} onNavigateTab={navigateTab} /> : null}
      <VariantSelectorSheet visible={variantSheetVisible} product={variantProduct} onClose={() => setVariantSheetVisible(false)} onConfirmAddToCart={() => {}} />
    </View>
  );
};

export const RootNavigator: React.FC = () => {
  const [hasCompletedOnboarding, setHasCompletedOnboarding] = useState<boolean | null>(null);
  const [initialLanguage, setInitialLanguage] = useState<MvpAppLanguage>('fr');

  useEffect(() => {
    let isMounted = true;

    void Promise.all([
      AsyncStorage.getItem(ONBOARDING_COMPLETE_KEY),
      AsyncStorage.getItem(LANGUAGE_KEY),
    ]).then(([onboardingValue, storedLanguage]) => {
      if (!isMounted) return;
      setHasCompletedOnboarding(onboardingValue === 'true');
      if (storedLanguage === 'fr' || storedLanguage === 'ar') {
        setInitialLanguage(storedLanguage);
      }
    }).catch(() => {
      if (isMounted) setHasCompletedOnboarding(false);
    });

    return () => { isMounted = false; };
  }, []);

  const rememberLanguage = (language: MvpAppLanguage) => {
    setInitialLanguage(language);
    void AsyncStorage.setItem(LANGUAGE_KEY, language).catch(() => undefined);
  };

  const completeOnboarding = () => {
    setHasCompletedOnboarding(true);
    void AsyncStorage.setItem(ONBOARDING_COMPLETE_KEY, 'true').catch(() => undefined);
  };

  return (
    <ThemeProvider initialLanguage={initialLanguage}>
      <RootNavigatorContent
        hasCompletedOnboarding={hasCompletedOnboarding}
        onLanguageSelected={rememberLanguage}
        onOnboardingCompleted={completeOnboarding}
      />
    </ThemeProvider>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1 },
});
