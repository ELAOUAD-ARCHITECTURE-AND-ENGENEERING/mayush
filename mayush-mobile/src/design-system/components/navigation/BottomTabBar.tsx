import React from 'react';
import { View, TouchableOpacity, StyleSheet } from 'react-native';
import { MayushText } from '../typography/MayushText';
import { CountBadge } from './CountBadge';
import { MayushIcon, MayushIconName } from './MayushIcon';
import { colors } from '../../tokens/colors';
import { useTheme } from '../../theme/useTheme';

export type TabKey = 'home' | 'categories' | 'wishlist' | 'cart' | 'account';

interface TabItemConfig {
  key: TabKey;
  labelFr: string;
  labelAr: string;
  icon: MayushIconName;
}

const tabs: TabItemConfig[] = [
  { key: 'home', labelFr: 'Accueil', labelAr: '\u0627\u0644\u0631\u0626\u064a\u0633\u064a\u0629', icon: 'home' },
  { key: 'categories', labelFr: 'Cat\u00e9gories', labelAr: '\u0627\u0644\u0623\u0642\u0633\u0627\u0645', icon: 'grid' },
  { key: 'wishlist', labelFr: 'Favoris', labelAr: '\u0627\u0644\u0645\u0641\u0636\u0644\u0629', icon: 'heart' },
  { key: 'cart', labelFr: 'Panier', labelAr: '\u0627\u0644\u0633\u0644\u0629', icon: 'shopping-cart' },
  { key: 'account', labelFr: 'Compte', labelAr: '\u062d\u0633\u0627\u0628\u064a', icon: 'user' },
];

export interface BottomTabBarProps {
  activeTab: TabKey;
  onTabPress: (key: TabKey) => void;
  cartBadgeCount?: number;
}

export const BottomTabBar: React.FC<BottomTabBarProps> = ({
  activeTab,
  onTabPress,
  cartBadgeCount = 0,
}) => {
  const { isRTL, language } = useTheme();
  const renderedTabs = isRTL ? [...tabs].reverse() : tabs;

  return (
    <View style={styles.bar}>
      {renderedTabs.map((tab) => {
        const active = tab.key === activeTab;
        const color = active ? colors.brand.orange500 : colors.neutral.gray700;
        const badge = tab.key === 'cart' ? cartBadgeCount : 0;

        return (
          <TouchableOpacity
            key={tab.key}
            activeOpacity={0.75}
            onPress={() => onTabPress(tab.key)}
            hitSlop={{ top: 6, right: 4, bottom: 6, left: 4 }}
            focusable={false}
            style={styles.item}
            accessibilityRole="tab"
            accessibilityState={{ selected: active }}
          >
            <View style={styles.iconWrap}>
              <MayushIcon name={tab.icon} size={25} color={color} strokeWidth={active ? 2.4 : 1.8} />
              {badge > 0 ? <View style={styles.badge}><CountBadge count={badge} /></View> : null}
            </View>
            <MayushText
              variant="caption"
              color={color}
              style={[styles.label, active && styles.activeLabel]}
            >
              {language === 'ar' ? tab.labelAr : tab.labelFr}
            </MayushText>
          </TouchableOpacity>
        );
      })}
    </View>
  );
};

const styles = StyleSheet.create({
  bar: {
    minHeight: 64,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-around',
    backgroundColor: colors.surface.white,
    borderTopWidth: 1,
    borderTopColor: colors.surface.borderWarm,
    paddingTop: 6,
    paddingBottom: 6,
    shadowColor: '#101D35',
    shadowOpacity: 0.05,
    shadowRadius: 10,
    shadowOffset: { width: 0, height: -3 },
    elevation: 8,
  },
  item: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 3,
  },
  iconWrap: {
    position: 'relative',
    height: 24,
    justifyContent: 'center',
  },
  badge: {
    position: 'absolute',
    top: -8,
    right: -13,
  },
  label: {
    fontSize: 10,
    lineHeight: 12,
  },
  activeLabel: {
    fontWeight: '700',
  },
});
