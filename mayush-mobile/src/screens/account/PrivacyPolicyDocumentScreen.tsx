import React from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { PRIVACY_POLICY_DOCUMENT } from '../../content/legalContent';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface PrivacyPolicyDocumentScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateHelpCenter?: () => void;
}

export const PrivacyPolicyDocumentScreen: React.FC<PrivacyPolicyDocumentScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateHelpCenter,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const doc = PRIVACY_POLICY_DOCUMENT;

  return (
    <View style={styles.container}>
      {/* Top Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? doc.titleAr : doc.titleFr}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Document Header Card */}
        <View style={styles.metaCard}>
          <View style={[styles.metaHeaderRow, isRTL && styles.rtlRow]}>
            <MayushIcon name="shield" size={20} color={colors.brand.orange500} />
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {isRTL ? 'المملكة المغربية — القانون 09-08' : 'Royaume du Maroc — Loi 09-08'}
            </MayushText>
          </View>
          <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
            {isRTL ? doc.lastUpdatedAr : doc.lastUpdatedFr}
          </MayushText>
        </View>

        {/* Long Document Sections */}
        <View style={styles.documentCard}>
          {doc.sections.map((sec, idx) => (
            <React.Fragment key={sec.id}>
              {idx > 0 && <View style={styles.divider} />}
              <View style={styles.sectionBlock}>
                <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>
                  {isRTL ? sec.titleAr : sec.titleFr}
                </MayushText>
                <MayushText variant="body" color={colors.neutral.gray700} style={[styles.paragraphText, isRTL && styles.rtlText]}>
                  {isRTL ? sec.contentAr : sec.contentFr}
                </MayushText>
              </View>
            </React.Fragment>
          ))}
        </View>

        {/* Contact Footer Card (Links to 309:805 Help Center) */}
        <TouchableOpacity style={styles.metaCard} onPress={onNavigateHelpCenter} activeOpacity={0.7}>
          <View style={[styles.metaHeaderRow, isRTL && styles.rtlRow]}>
            <MayushIcon name="help-circle" size={18} color={colors.brand.orange500} />
            <MayushText variant="strongBody" color={colors.brand.navy900} style={[{ flex: 1 }, isRTL && styles.rtlText]}>
              {isRTL ? 'تواصل مع الدعم / مركز المساعدة' : 'Contact & Réclamations (Centre d\'aide)'}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
          </View>
          <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
            {isRTL
              ? 'البريد الإلكتروني: contact@mayush.ma | الموقع: www.mayush.ma'
              : 'E-mail : contact@mayush.ma | Site officiel : www.mayush.ma'}
          </MayushText>
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
  scrollContent: { padding: spacing.md, gap: spacing.md, paddingBottom: 100 },
  metaCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300, gap: spacing.xs,
  },
  metaHeaderRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs },
  documentCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300, gap: spacing.md,
  },
  sectionBlock: { gap: spacing.xs },
  sectionTitle: { fontSize: 15, fontWeight: '700' },
  paragraphText: { fontSize: 14, lineHeight: 22, color: colors.neutral.gray700 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300 },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
