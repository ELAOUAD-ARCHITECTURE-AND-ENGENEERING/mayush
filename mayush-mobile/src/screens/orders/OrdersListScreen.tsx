import React, { useState } from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { formatMadPrice } from '../../commerce/cartState';
import { BuyerOrder, getDeliveryStatusLabel } from '../../commerce/orderState';
import { filterOrdersByTab, getOrderCardDirection, getOrdersTabDirection, INITIAL_ORDER_TAB, isGlobalOrdersEmpty, OrderTab, reduceOrderTabSelection } from '../../commerce/orderTabs';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

const PRODUCT = require('../../../assets/reference-art/home-new-luna.png');

export interface OrdersListScreenProps {
  orders: BuyerOrder[];
  onOpenOrder: (orderId: string) => void;
  onNavigateTab: (tab: TabKey) => void;
}

export const OrdersListScreen: React.FC<OrdersListScreenProps> = ({ orders, onOpenOrder, onNavigateTab }) => {
  const { language, isRTL } = useTheme();
  const [selectedOrderTab, setSelectedOrderTab] = useState<OrderTab>(INITIAL_ORDER_TAB);
  const visibleOrders = filterOrdersByTab(orders, selectedOrderTab);
  const copy = language === 'ar'
    ? {
        title: '\u0637\u0644\u0628\u0627\u062a\u064a',
        subtitle: '\u062a\u0627\u0628\u0639 \u0633\u062c\u0644 \u0637\u0644\u0628\u0627\u062a\u0643.',
        all: '\u0627\u0644\u0643\u0644',
        inProgress: '\u0642\u064a\u062f \u0627\u0644\u062a\u0646\u0641\u064a\u0630',
        completed: '\u0645\u0643\u062a\u0645\u0644\u0629',
        cancelled: '\u0645\u0644\u063a\u0627\u0629',
        details: '\u0639\u0631\u0636 \u0627\u0644\u062a\u0641\u0627\u0635\u064a\u0644',
        repositoryEmpty: '\u0644\u0627 \u062a\u0648\u062c\u062f \u0637\u0644\u0628\u0627\u062a \u062d\u062a\u0649 \u0627\u0644\u0622\u0646.',
        tabEmpty: '\u0644\u0627 \u062a\u0648\u062c\u062f \u0637\u0644\u0628\u0627\u062a \u0641\u064a \u0647\u0630\u0647 \u0627\u0644\u0641\u0626\u0629.',
        search: '\u0627\u0628\u062d\u062b \u0628\u0631\u0642\u0645 \u0627\u0644\u0637\u0644\u0628',
      }
    : {
        title: 'Mes commandes',
        subtitle: 'Consultez l\u2019historique de toutes vos commandes.',
        all: 'Toutes',
        inProgress: 'En cours',
        completed: 'Termin\u00e9es',
        cancelled: 'Annul\u00e9es',
        details: 'Voir les d\u00e9tails',
        repositoryEmpty: 'Aucune commande pour le moment.',
        tabEmpty: 'Aucune commande dans cette cat\u00e9gorie.',
        search: 'Rechercher par num\u00e9ro de commande',
      };
  const tabs: Array<{ key: OrderTab; label: string }> = [
    { key: 'all', label: copy.all },
    { key: 'in_progress', label: copy.inProgress },
    { key: 'completed', label: copy.completed },
    { key: 'cancelled', label: copy.cancelled },
  ];

  return (
    <View style={styles.screen} accessibilityLabel={copy.title}>
      <View style={[styles.header, isRTL && styles.rowReverse]}>
        <MayushLogo width={145} height={43} />
        <MayushIcon name="bell" size={25} color={colors.brand.navy900} />
      </View>
      <MayushText variant="display" color={colors.brand.navy900} align="center" style={[styles.title, isRTL && styles.rtlText]}>{copy.title}</MayushText>
      <MayushText variant="body" color={colors.neutral.gray700} align="center" style={[styles.subtitle, isRTL && styles.rtlText]}>{copy.subtitle}</MayushText>
      <View style={[styles.search, isRTL && styles.rowReverse]}>
        <MayushIcon name="search" size={18} color={colors.neutral.gray700} />
        <MayushText variant="caption" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>{copy.search}</MayushText>
      </View>
      <View style={[styles.tabs, { flexDirection: getOrdersTabDirection(isRTL) }]}>
        {tabs.map((tab) => (
          <Tab
            key={tab.key}
            label={tab.label}
            active={selectedOrderTab === tab.key}
            onPress={() => setSelectedOrderTab((current) => reduceOrderTabSelection(current, tab.key))}
          />
        ))}
      </View>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.list}>
        {visibleOrders.length
          ? visibleOrders.map((order) => <OrderCard key={order.orderId} order={order} language={language} isRTL={isRTL} label={copy.details} onPress={() => onOpenOrder(order.orderId)} />)
          : <MayushText variant="body" color={colors.neutral.gray700} align="center" style={[styles.empty, isRTL && styles.rtlText]}>{isGlobalOrdersEmpty(orders) ? copy.repositoryEmpty : copy.tabEmpty}</MayushText>}
      </ScrollView>
      <BottomTabBar activeTab="account" onTabPress={onNavigateTab} cartBadgeCount={0} />
    </View>
  );
};

const Tab: React.FC<{ label: string; active: boolean; onPress: () => void }> = ({ label, active, onPress }) => (
  <TouchableOpacity accessibilityRole="tab" accessibilityState={{ selected: active }} accessibilityLabel={label} onPress={onPress} style={[styles.tab, active && styles.tabActive]}>
    <MayushText variant="caption" color={active ? colors.brand.orange500 : colors.brand.navy900}>{label}</MayushText>
  </TouchableOpacity>
);

const OrderCard: React.FC<{ order: BuyerOrder; language: 'fr' | 'ar'; isRTL: boolean; label: string; onPress: () => void }> = ({ order, language, isRTL, label, onPress }) => {
  const deliveryLabel = getDeliveryStatusLabel(order.deliveryStatus, language);
  const statusColor = order.deliveryStatus === 'delivered' ? colors.semantic.success : colors.brand.orange500;
  return (
    <View style={styles.card} accessibilityLabel={`${order.orderId} ${deliveryLabel}`}>
      <View style={[styles.cardTop, { flexDirection: getOrderCardDirection(isRTL) }]}>
        <View style={isRTL && styles.rtlContent}>
          <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.ltrValue}>{order.orderId}</MayushText>
          <MayushText variant="caption" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>{order.createdAtLabel}</MayushText>
        </View>
        <View style={[styles.status, isRTL && styles.rowReverse]}>
          <MayushIcon name={order.deliveryStatus === 'delivered' ? 'check-circle' : 'clock'} size={14} color={statusColor} />
          <MayushText variant="caption" color={statusColor} style={isRTL && styles.rtlText}>{deliveryLabel}</MayushText>
        </View>
      </View>
      <View style={[styles.images, isRTL && styles.rowReverse]}>
        {order.lines.slice(0, 3).map((line) => <Image key={line.orderLineId} source={line.imageUri ? { uri: line.imageUri } : PRODUCT} style={styles.product} />)}
        <MayushText variant="caption" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>{order.lines.length} {language === 'ar' ? '\u0645\u0646\u062a\u062c\u0627\u062a' : 'articles'}</MayushText>
        <MayushText variant="sectionTitle" color={colors.brand.orange500} style={[styles.amount, styles.ltrValue]}>{formatMadPrice(order.totalMad)}</MayushText>
      </View>
      <View style={[styles.cardBottom, isRTL && styles.rowReverse]}>
        <View style={isRTL && styles.rtlContent}>
          <MayushText variant="caption" color={colors.semantic.success} style={isRTL && styles.rtlText}>{language === 'ar' ? '\u0627\u0644\u062f\u0641\u0639' : '\u2713 Paiement'}</MayushText>
          <MayushText variant="caption" color={colors.semantic.success} style={isRTL && styles.rtlText}>{language === 'ar' ? '\u0645\u062f\u0641\u0648\u0639' : 'Pay\u00e9'}</MayushText>
        </View>
        <View style={isRTL && styles.rtlContent}>
          <MayushText variant="caption" color={statusColor} style={isRTL && styles.rtlText}>{language === 'ar' ? '\u0627\u0644\u062a\u0648\u0635\u064a\u0644' : 'Livraison'}</MayushText>
          <MayushText variant="caption" color={statusColor} style={isRTL && styles.rtlText}>{deliveryLabel}</MayushText>
        </View>
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={`${label} ${order.orderId}`} onPress={onPress} style={[styles.detailButton, isRTL && styles.rowReverse]}>
          <MayushText variant="caption" color={colors.brand.orange500} style={isRTL && styles.rtlText}>{label}</MayushText>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={14} color={colors.brand.orange500} />
        </TouchableOpacity>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFFDF9' },
  header: { height: 72, paddingHorizontal: 25, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  title: { marginTop: 2, fontSize: 29, lineHeight: 34 },
  subtitle: { marginTop: 2, fontSize: 12 },
  search: { height: 36, marginTop: 11, marginHorizontal: 27, paddingHorizontal: 12, borderWidth: 1, borderColor: colors.neutral.gray300, borderRadius: 9, flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: colors.surface.white },
  tabs: { height: 39, marginTop: 4, paddingHorizontal: 20, justifyContent: 'space-between', borderBottomWidth: 1, borderBottomColor: colors.surface.borderWarm },
  tab: { minWidth: 57, alignItems: 'center', justifyContent: 'center' },
  tabActive: { borderBottomWidth: 2, borderBottomColor: colors.brand.orange500 },
  list: { padding: 11, gap: 9 },
  card: { borderWidth: 1, borderColor: '#F0E3D7', borderRadius: 8, padding: 11, backgroundColor: colors.surface.white, shadowColor: colors.brand.navy900, shadowOpacity: 0.06, shadowRadius: 7, shadowOffset: { width: 0, height: 2 }, elevation: 2 },
  cardTop: { flexDirection: 'row', justifyContent: 'space-between' },
  status: { height: 23, borderRadius: 5, paddingHorizontal: 8, flexDirection: 'row', alignItems: 'center', gap: 3, backgroundColor: '#FFF5E9' },
  images: { minHeight: 47, marginTop: 7, flexDirection: 'row', alignItems: 'center', gap: 4 },
  product: { width: 47, height: 40, borderRadius: 4, backgroundColor: colors.surface.cream },
  amount: { marginLeft: 'auto', fontSize: 15 },
  cardBottom: { marginTop: 7, paddingTop: 7, borderTopWidth: 1, borderTopColor: colors.surface.borderWarm, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  detailButton: { height: 27, borderWidth: 1, borderColor: colors.brand.orange500, borderRadius: 5, paddingHorizontal: 8, flexDirection: 'row', alignItems: 'center', gap: 3 },
  empty: { marginTop: 50 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl', textAlign: 'right' },
  rtlContent: { alignItems: 'flex-end' },
  ltrValue: { writingDirection: 'ltr' },
});
