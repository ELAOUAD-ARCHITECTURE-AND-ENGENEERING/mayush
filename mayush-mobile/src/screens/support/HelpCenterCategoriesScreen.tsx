import React from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { supportState } from '../../commerce/supportState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface HelpCenterCategoriesScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateFaq?: () => void;
  onNavigateRecentRequests?: () => void;
  onNavigateOrders?: () => void;
  onNavigateAccountSettings?: () => void;
}

export const HelpCenterCategoriesScreen: React.FC<HelpCenterCategoriesScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateFaq,
  onNavigateRecentRequests,
  onNavigateOrders,
  onNavigateAccountSettings,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const categories = supportState.getFaqCategories();

  const helpTopics = [
    { id: 'orders', icon: 'package', label: isRTL ? 'الطلبات والتوصيل' : 'Commandes & Livraison', onPress: onNavigateOrders },
    { id: 'payments', icon: 'credit-card', label: isRTL ? 'الدفع والفواتير' : 'Paiement & Facturation', onPress: onNavigateFaq },
    { id: 'account', icon: 'user', label: isRTL ? 'حسابي والأمان' : 'Mon Compte & Sécurité', onPress: onNavigateAccountSettings },
    { id: 'returns', icon: 'rotate-ccw', label: isRTL ? 'الإرجاع والاسترداد' : 'Retours & Remboursements', onPress: onNavigateFaq },
    { id: 'products', icon: 'box', label: isRTL ? 'المنتجات والضمان' : 'Produits & Garantie', onPress: onNavigateFaq },
  ];

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'مركز المساعدة' : "Centre d'Aide"}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      {/* Search area */}
      <View style={styles.searchArea}>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.heroTitle, isRTL && styles.rtlText]}>
          {isRTL ? 'كيف يمكننا مساعدتك؟' : 'Comment pouvons-nous vous aider ?'}
        </MayushText>
        <View style={[styles.searchBox, isRTL && styles.rtlRow]}>
          <MayushIcon name="search" size={18} color={colors.neutral.gray500} />
          <MayushText variant="body" color={colors.neutral.gray500} style={styles.searchInput}>
            {isRTL ? 'ابحث في مركز المساعدة...' : 'Rechercher dans l\'aide...'}
          </MayushText>
        </View>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Quick Access Cards */}
        <View style={styles.quickAccessRow}>
          <TouchableOpacity style={[styles.quickCard, isRTL && styles.rtlRow]} onPress={onNavigateFaq} activeOpacity={0.7}>
            <View style={[styles.quickIconBox, { backgroundColor: 'rgba(217,116,52,0.1)' }]}>
              <MayushIcon name="help-circle" size={22} color={colors.brand.orange500} />
            </View>
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {isRTL ? 'الأسئلة الشائعة' : 'FAQ'}
            </MayushText>
          </TouchableOpacity>

          <TouchableOpacity style={[styles.quickCard, isRTL && styles.rtlRow]} onPress={onNavigateRecentRequests} activeOpacity={0.7}>
            <View style={[styles.quickIconBox, { backgroundColor: 'rgba(30,58,95,0.1)' }]}>
              <MayushIcon name="file-text" size={22} color={colors.brand.navy900} />
            </View>
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {isRTL ? 'طلباتي السابقة' : 'Mes Demandes'}
            </MayushText>
          </TouchableOpacity>
        </View>

        {/* Help Topics */}
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionLabel, isRTL && styles.rtlText]}>
          {isRTL ? 'المواضيع' : 'Sujets d\'aide'}
        </MayushText>

        {helpTopics.map((topic) => (
          <TouchableOpacity
            key={topic.id}
            style={[styles.topicRow, isRTL && styles.rtlRow]}
            onPress={topic.onPress}
            activeOpacity={0.7}
          >
            <View style={styles.topicIconBox}>
              <MayushIcon name={(topic.icon || 'help-circle') as MayushIconName} size={20} color={colors.brand.orange500} />
            </View>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={[styles.topicLabel, isRTL && styles.rtlText]}>
              {topic.label}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
          </TouchableOpacity>
        ))}
      </ScrollView>

      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.neutral.gray100 },
  header: {
    height: 56, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: spacing.md, backgroundColor: colors.neutral.white,
    borderBottomWidth: 1, borderBottomColor: colors.neutral.gray300,
  },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  backButton: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  searchArea: {
    backgroundColor: colors.neutral.white, paddingHorizontal: spacing.md,
    paddingBottom: spacing.md,
  },
  heroTitle: { fontSize: 20, fontWeight: '700', marginBottom: spacing.sm },
  searchBox: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.gray100, borderRadius: 12, paddingHorizontal: spacing.sm, height: 44,
  },
  searchInput: { flex: 1 },
  scrollContent: { padding: spacing.md, gap: spacing.xs, paddingBottom: 100 },
  quickAccessRow: { flexDirection: 'row', gap: spacing.sm, marginBottom: spacing.sm },
  quickCard: {
    flex: 1, flexDirection: 'row', alignItems: 'center', gap: spacing.sm,
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  quickIconBox: {
    width: 40, height: 40, borderRadius: 10, alignItems: 'center', justifyContent: 'center',
  },
  sectionLabel: { fontSize: 16, fontWeight: '700', marginBottom: spacing.xs, marginTop: spacing.sm },
  topicRow: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.md,
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  topicIconBox: {
    width: 40, height: 40, borderRadius: 10, backgroundColor: 'rgba(217,116,52,0.08)',
    alignItems: 'center', justifyContent: 'center',
  },
  topicLabel: { flex: 1 },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
