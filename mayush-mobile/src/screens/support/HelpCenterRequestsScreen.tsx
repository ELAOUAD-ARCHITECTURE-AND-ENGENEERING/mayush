import React from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { supportState } from '../../commerce/supportState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface HelpCenterRequestsScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateSupportHub?: () => void;
  onSelectRequest?: (requestId: string) => void;
}

export const HelpCenterRequestsScreen: React.FC<HelpCenterRequestsScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateSupportHub,
  onSelectRequest,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const requests = supportState.getSupportRequests();

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'in-progress': return colors.semantic.warning;
      case 'resolved': return colors.semantic.success;
      case 'closed': return colors.neutral.gray500;
      default: return colors.brand.orange500;
    }
  };

  const getStatusBgColor = (status: string) => {
    switch (status) {
      case 'in-progress': return colors.semantic.warningBackground;
      case 'resolved': return colors.semantic.successBackground;
      case 'closed': return colors.neutral.gray300;
      default: return 'rgba(217,116,52,0.1)';
    }
  };

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'طلبات الدعم' : 'Mes Demandes'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>
          {isRTL ? 'الطلبات الأخيرة' : 'Demandes récentes'}
        </MayushText>

        {requests.map((req) => (
          <TouchableOpacity
            key={req.id}
            style={styles.requestCard}
            onPress={() => {
              supportState.setSelectedSupportRequestId(req.id);
              onSelectRequest?.(req.id);
            }}
            activeOpacity={0.7}
          >
            <View style={[styles.requestHeaderRow, isRTL && styles.rtlRow]}>
              <View style={[styles.statusBadge, { backgroundColor: getStatusBgColor(req.status) }]}>
                <MayushText variant="caption" color={getStatusColor(req.status)}>
                  {isRTL ? req.statusLabelAr : req.statusLabel}
                </MayushText>
              </View>
              <MayushText variant="caption" color={colors.neutral.gray500}>{req.date}</MayushText>
            </View>

            <MayushText variant="strongBody" color={colors.brand.navy900} style={[styles.requestTitle, isRTL && styles.rtlText]}>
              {isRTL ? req.titleAr : req.title}
            </MayushText>

            <View style={[styles.refRow, isRTL && styles.rtlRow]}>
              <MayushText variant="caption" color={colors.neutral.gray500}>
                {isRTL ? 'المرجع:' : 'Réf :'} {req.reference}
              </MayushText>
            </View>

            <MayushText variant="smallBody" color={colors.neutral.gray700} numberOfLines={2} style={[styles.summaryText, isRTL && styles.rtlText]}>
              {isRTL ? req.summaryAr : req.summary}
            </MayushText>
          </TouchableOpacity>
        ))}

        {/* Navigate to Support Hub */}
        <TouchableOpacity
          style={[styles.supportHubCard, isRTL && styles.rtlRow]}
          onPress={onNavigateSupportHub}
          activeOpacity={0.85}
        >
          <View style={styles.supportIconBox}>
            <MayushIcon name="headphones" size={22} color={colors.brand.orange500} />
          </View>
          <View style={styles.supportTextCol}>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {isRTL ? 'تحتاج مساعدة إضافية؟' : 'Besoin d\'aide supplémentaire ?'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {isRTL ? 'تواصل مع فريق الدعم' : 'Contactez notre équipe support'}
            </MayushText>
          </View>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
        </TouchableOpacity>
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
  scrollContent: { padding: spacing.md, gap: spacing.sm, paddingBottom: 100 },
  sectionTitle: { fontSize: 16, fontWeight: '700' },
  requestCard: {
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  requestHeaderRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: spacing.xs },
  statusBadge: { paddingHorizontal: 10, paddingVertical: 3, borderRadius: 6 },
  requestTitle: { lineHeight: 22, marginBottom: 4 },
  refRow: { flexDirection: 'row', marginBottom: spacing.xs },
  summaryText: { lineHeight: 20 },
  supportHubCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.md,
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.brand.orange500, marginTop: spacing.sm,
  },
  supportIconBox: {
    width: 44, height: 44, borderRadius: 12, backgroundColor: 'rgba(217,116,52,0.1)',
    alignItems: 'center', justifyContent: 'center',
  },
  supportTextCol: { flex: 1 },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
