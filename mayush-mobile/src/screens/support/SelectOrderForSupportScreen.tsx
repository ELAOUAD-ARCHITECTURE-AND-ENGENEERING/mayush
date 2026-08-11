import React, { useState, useEffect } from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
} from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { supportState } from '../../commerce/supportState';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { getDeliveryStatusLabel, getOrderCreatedAtLabel, orderState } from '../../commerce/orderState';
import { formatMadPrice } from '../../commerce/cartState';

interface SelectOrderForSupportScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onSelectOrder: (orderId?: string) => void;
}

export const SelectOrderForSupportScreen: React.FC<SelectOrderForSupportScreenProps> = ({
  onNavigateTab,
  onBack,
  onSelectOrder,
}) => {
  const [selectedId, setSelectedId] = useState<string>(supportState.getContactDraft().selectedOrderId || orderState.getSelectedOrderId() || '');
  const [searchQuery, setSearchQuery] = useState('');
  const [language, setLanguage] = useState(accountPreferencesState.getLanguage());
  const [, setOrderRevision] = useState(0);

  useEffect(() => {
    const unsubPref = accountPreferencesState.subscribe(() => {
      setLanguage(accountPreferencesState.getLanguage());
    });
    const unsubOrders = orderState.subscribe(() => setOrderRevision((revision) => revision + 1));
    return () => { unsubPref(); unsubOrders(); };
  }, []);

  const isRTL = language === 'ar';
  const orderLocale: 'fr' | 'ar' = isRTL ? 'ar' : 'fr';

  const filteredOrders = orderState.getOrders().filter((order) =>
    order.orderId.toLowerCase().includes(searchQuery.trim().toLowerCase())
  );

  const handleConfirmSelection = (orderRef?: string) => {
    const targetRef = orderRef || selectedId;
    supportState.setContactDraft({ selectedOrderId: targetRef });
    onSelectOrder(targetRef);
  };

  const handleClearSelection = () => {
    supportState.setContactDraft({ selectedOrderId: undefined });
    onSelectOrder(undefined);
  };

  const getStatusBadgeStyle = (type: string) => {
    switch (type) {
      case 'in-progress':
        return { bg: '#FFF8E6', text: '#D97706', icon: 'clock' as const };
      case 'completed':
        return { bg: '#EDFCF2', text: '#0D894F', icon: 'check-circle' as const };
      case 'cancelled':
        return { bg: '#FDE8E8', text: '#D9381E', icon: 'x-circle' as const };
      default:
        return { bg: colors.neutral.gray100, text: colors.neutral.gray700, icon: 'help-circle' as const };
    }
  };

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onBack} style={styles.backBtn} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="cardTitle" style={styles.headerLogo}>
          MAYUSH<MayushText variant="cardTitle" color={colors.brand.orange500}> DESIGN</MayushText>
        </MayushText>
        <TouchableOpacity onPress={() => handleConfirmSelection()} style={styles.bellBtn} activeOpacity={0.7}>
          <MayushIcon name="bell" size={20} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Header Illustration */}
        <View style={styles.titleSection}>
          <View style={styles.iconCircle}>
            <MayushIcon name="shopping-bag" size={28} color={colors.brand.orange500} />
          </View>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
            {isRTL ? 'تحديد طلب' : 'Sélectionner une commande'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray500} style={[styles.subtitle, isRTL && styles.rtlText]}>
            {isRTL
              ? 'اختر الطلب الذي ترغب في الحصول على المساعدة بشأنه.'
              : 'Choisissez une commande pour laquelle vous souhaitez obtenir de l’aide.'}
          </MayushText>
        </View>

        {/* Search Input */}
        <View style={styles.searchBox}>
          <MayushIcon name="search" size={18} color={colors.neutral.gray500} />
          <TextInput
            style={[styles.searchInput, isRTL && styles.rtlText]}
            placeholder={isRTL ? 'البحث عن طريق رقم الطلب' : 'Rechercher par numéro de commande'}
            placeholderTextColor={colors.neutral.gray500}
            value={searchQuery}
            onChangeText={setSearchQuery}
          />
          {searchQuery ? (
            <TouchableOpacity onPress={() => setSearchQuery('')}>
              <MayushIcon name="x" size={16} color={colors.neutral.gray500} />
            </TouchableOpacity>
          ) : null}
        </View>

        {/* Section Header */}
        <MayushText variant="strongBody" color={colors.brand.navy900} style={[styles.sectionHeader, isRTL && styles.rtlText]}>
          {isRTL ? 'الطلبات الأخيرة' : 'Commandes récentes'}
        </MayushText>

        {/* Orders List */}
        {filteredOrders.map((order) => {
          const isSelected = selectedId === order.orderId;
          const statusType = order.deliveryStatus === 'delivered' ? 'completed' : order.deliveryStatus === 'cancelled' ? 'cancelled' : 'in-progress';
          const badge = getStatusBadgeStyle(statusType);
          return (
            <TouchableOpacity
              key={order.orderId}
              style={[styles.orderCard, isSelected && styles.orderCardSelected]}
              onPress={() => setSelectedId(order.orderId)}
              activeOpacity={0.85}
            >
              <View style={styles.cardHeader}>
                <View style={styles.cardRefRow}>
                  <View style={[styles.clipboardCircle, isSelected && styles.clipboardCircleSelected]}>
                    <MayushIcon name="file-text" size={18} color={isSelected ? colors.brand.orange500 : colors.neutral.gray500} />
                  </View>
                  <View>
                    <MayushText variant="strongBody" color={colors.brand.navy900}>
                      {order.orderId}
                    </MayushText>
                    <MayushText variant="smallBody" color={colors.neutral.gray500}>
                      {getOrderCreatedAtLabel(order, orderLocale)}
                    </MayushText>
                  </View>
                </View>

                <View style={[styles.statusBadge, { backgroundColor: badge.bg }]}>
                  <MayushIcon name={badge.icon} size={12} color={badge.text} />
                  <MayushText variant="smallBody" color={badge.text} style={{ fontWeight: '600', marginLeft: 4 }}>
                    {getDeliveryStatusLabel(order.deliveryStatus, orderLocale)}
                  </MayushText>
                </View>
              </View>

              <View style={styles.cardFooter}>
                <View style={styles.countBadge}>
                  <MayushText variant="smallBody" color={colors.neutral.gray700}>
                    {order.lines.length} {isRTL ? 'منتجات' : (order.lines.length > 1 ? 'articles' : 'article')}
                  </MayushText>
                </View>
                <MayushText variant="strongBody" color={colors.brand.orange500} style={{ fontSize: 14 }}>
                  {formatMadPrice(order.totalMad)}
                </MayushText>
                <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
              </View>
            </TouchableOpacity>
          );
        })}

        {/* Primary Confirmation CTA */}
        <TouchableOpacity style={styles.primaryBtn} onPress={() => handleConfirmSelection()} activeOpacity={0.85}>
          <MayushText variant="strongBody" color={colors.neutral.white}>
            {isRTL ? 'تأكيد تحديد الطلب' : 'Sélectionner une commande'}
          </MayushText>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.white} />
        </TouchableOpacity>

        {/* Secondary Clear Selection CTA */}
        <TouchableOpacity style={styles.secondaryBtn} onPress={handleClearSelection} activeOpacity={0.85}>
          <MayushIcon name="headphones" size={18} color={colors.brand.navy900} />
          <MayushText variant="strongBody" color={colors.brand.navy900}>
            {isRTL ? 'المتابعة بدون طلب' : 'Continuer sans commande'}
          </MayushText>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.brand.navy900} />
        </TouchableOpacity>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FAF8F5' },
  header: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: spacing.md, paddingTop: 48, paddingBottom: spacing.sm,
    backgroundColor: '#FAF8F5',
  },
  backBtn: { padding: spacing.xs },
  headerLogo: { fontSize: 18, fontWeight: '800', color: colors.brand.navy900 },
  bellBtn: { padding: spacing.xs },
  scrollView: { flex: 1 },
  scrollContent: { padding: spacing.md, paddingBottom: 40 },
  titleSection: { alignItems: 'center', marginBottom: spacing.md },
  iconCircle: {
    width: 56, height: 56, borderRadius: 28, backgroundColor: 'rgba(232,125,62,0.1)',
    alignItems: 'center', justifyContent: 'center', marginBottom: spacing.xs,
  },
  title: { textAlign: 'center', marginBottom: 4 },
  subtitle: { textAlign: 'center', fontSize: 13, lineHeight: 18 },
  searchBox: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.white, borderWidth: 1, borderColor: colors.neutral.gray300,
    borderRadius: 12, paddingHorizontal: spacing.sm, paddingVertical: 10, marginBottom: spacing.md,
  },
  searchInput: { flex: 1, fontSize: 13, color: colors.brand.navy900 },
  sectionHeader: { marginBottom: spacing.xs, fontSize: 14 },
  orderCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    marginBottom: spacing.sm, borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)',
  },
  orderCardSelected: { borderColor: colors.brand.orange500, backgroundColor: '#FFF2EB' },
  cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  cardRefRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs },
  clipboardCircle: {
    width: 36, height: 36, borderRadius: 18, backgroundColor: '#F3F5F7',
    alignItems: 'center', justifyContent: 'center',
  },
  clipboardCircleSelected: { backgroundColor: 'rgba(232,125,62,0.15)' },
  statusBadge: {
    flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 4,
    borderRadius: 12,
  },
  cardFooter: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    marginTop: 12, paddingTop: 10, borderTopWidth: 1, borderTopColor: colors.neutral.gray300,
  },
  countBadge: { backgroundColor: '#F3F5F7', borderRadius: 8, paddingHorizontal: 8, paddingVertical: 4 },
  primaryBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.brand.orange500, borderRadius: 14, paddingVertical: 14,
    marginTop: spacing.md, marginBottom: spacing.sm,
  },
  secondaryBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.white, borderWidth: 1, borderColor: colors.brand.navy900,
    borderRadius: 14, paddingVertical: 14,
  },
  rtlText: { textAlign: 'right' },
});
