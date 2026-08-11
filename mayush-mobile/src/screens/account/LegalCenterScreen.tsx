import React, { useState } from 'react';
import { Modal, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { TERMS_CONDITIONS_DOCUMENT } from '../../content/legalContent';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface LegalCenterScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigatePrivacyData?: () => void;
  onNavigatePrivacyPolicy?: () => void;
}

export const LegalCenterScreen: React.FC<LegalCenterScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigatePrivacyData,
  onNavigatePrivacyPolicy,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [activeModalDoc, setActiveModalDoc] = useState<'terms' | 'delivery' | 'cookies' | null>(null);

  const legalSections: {
    id: string;
    titleFr: string;
    titleAr: string;
    items: {
      id: string;
      icon: MayushIconName;
      titleFr: string;
      titleAr: string;
      subFr: string;
      subAr: string;
      onPress: () => void;
    }[];
  }[] = [
    {
      id: 'general',
      titleFr: 'Conditions & Confidentialité',
      titleAr: 'الشروط والخصوصية',
      items: [
        {
          id: 'cgu',
          icon: 'file-text',
          titleFr: 'Conditions Générales d\'Utilisation (CGU)',
          titleAr: 'الشروط العامة للاستخدام',
          subFr: 'Règles d\'utilisation et engagements de la plateforme',
          subAr: 'قواعد الاستخدام والتزامات المنصة',
          onPress: () => setActiveModalDoc('terms'),
        },
        {
          id: 'privacy',
          icon: 'lock',
          titleFr: 'Politique de Confidentialité & Données',
          titleAr: 'سياسة الخصوصية وحماية البيانات',
          subFr: 'Protection des données personnelles (Loi 09-08)',
          subAr: 'حماية المعطيات ذات الطابع الشخصي (القانون 09-08)',
          onPress: () => {
            if (onNavigatePrivacyData) {
              onNavigatePrivacyData();
            } else if (onNavigatePrivacyPolicy) {
              onNavigatePrivacyPolicy();
            }
          },
        },
      ],
    },
    {
      id: 'commercial',
      titleFr: 'Politiques Commerciales & Livraisons',
      titleAr: 'السياسات التجارية والتوصيل',
      items: [
        {
          id: 'delivery-returns',
          icon: 'truck',
          titleFr: 'Livraisons & Retours sous 14 jours',
          titleAr: 'التوصيل والإرجاع خلال 14 يوماً',
          subFr: 'Modalités de livraison au Maroc et droit de rétractation',
          subAr: 'شروط التوصيل في المغرب وحق الإرجاع',
          onPress: () => setActiveModalDoc('delivery'),
        },
        {
          id: 'mentions-cookies',
          icon: 'info',
          titleFr: 'Mentions Légales & Cookies',
          titleAr: 'الإشعارات القانونية والمملفات النصية',
          subFr: 'Éditeur Mayush Design SARL et gestion des cookies',
          subAr: 'بيانات الناشر وإدارة الملفات النصية',
          onPress: () => setActiveModalDoc('cookies'),
        },
      ],
    },
  ];

  return (
    <View style={styles.container}>
      {/* Top Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'المركز القانوني والشروط' : 'Centre Légal & Conditions'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {legalSections.map((sec) => (
          <View key={sec.id} style={styles.sectionBlock}>
            <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionTitleText, isRTL && styles.rtlText]}>
              {isRTL ? sec.titleAr : sec.titleFr}
            </MayushText>
            <View style={styles.card}>
              {sec.items.map((item, idx) => (
                <React.Fragment key={item.id}>
                  {idx > 0 && <View style={styles.divider} />}
                  <TouchableOpacity style={[styles.row, isRTL && styles.rtlRow]} onPress={item.onPress} activeOpacity={0.7}>
                    <View style={styles.iconBox}>
                      <MayushIcon name={item.icon} size={20} color={colors.brand.navy900} />
                    </View>
                    <View style={styles.textCol}>
                      <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                        {isRTL ? item.titleAr : item.titleFr}
                      </MayushText>
                      <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                        {isRTL ? item.subAr : item.subFr}
                      </MayushText>
                    </View>
                    <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
                  </TouchableOpacity>
                </React.Fragment>
              ))}
            </View>
          </View>
        ))}
      </ScrollView>

      {/* Terms / Info Modal */}
      <Modal visible={activeModalDoc !== null} animationType="slide" transparent={true} onRequestClose={() => setActiveModalDoc(null)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalCard}>
            <View style={[styles.modalHeader, isRTL && styles.rtlRow]}>
              <MayushText variant="sectionTitle" color={colors.brand.navy900}>
                {activeModalDoc === 'terms'
                  ? (isRTL ? 'Conditions Générales' : 'Conditions Générales (CGU)')
                  : activeModalDoc === 'delivery'
                  ? (isRTL ? 'Livraisons & Retours' : 'Livraisons & Retours')
                  : (isRTL ? 'Mentions Légales' : 'Mentions Légales & Cookies')}
              </MayushText>
              <TouchableOpacity onPress={() => setActiveModalDoc(null)} style={styles.closeBtn}>
                <MayushIcon name="x" size={20} color={colors.brand.navy900} />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.modalScroll} showsVerticalScrollIndicator={false}>
              {activeModalDoc === 'terms' && (
                <View style={styles.modalBody}>
                  {TERMS_CONDITIONS_DOCUMENT.sections.map((s) => (
                    <View key={s.id} style={styles.docSec}>
                      <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                        {isRTL ? s.titleAr : s.titleFr}
                      </MayushText>
                      <MayushText variant="body" color={colors.neutral.gray700} style={[styles.docPara, isRTL && styles.rtlText]}>
                        {isRTL ? s.contentAr : s.contentFr}
                      </MayushText>
                    </View>
                  ))}
                </View>
              )}

              {activeModalDoc === 'delivery' && (
                <View style={styles.modalBody}>
                  <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                    {isRTL ? 'التوصيل والإرجاع بالمهل القانونية' : 'Livraison à domicile au Maroc'}
                  </MayushText>
                  <MayushText variant="body" color={colors.neutral.gray700} style={[styles.docPara, isRTL && styles.rtlText]}>
                    {isRTL
                      ? 'توصيل سريع لكافة المدن المغربية. يحق لكم طلب إرجاع المنتجات خلال 7 أيام من الاستلام وفقاً للقانون 31-08.'
                      : 'Les livraisons sont assurées dans toutes les villes du Maroc sous 48h à 72h. Vous bénéficiez du droit de rétractation de 7 jours (Loi 31-08).'}
                  </MayushText>
                </View>
              )}

              {activeModalDoc === 'cookies' && (
                <View style={styles.modalBody}>
                  <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                    {isRTL ? 'الناشر والملفات النصية' : 'Éditeur & Politique de Cookies'}
                  </MayushText>
                  <MayushText variant="body" color={colors.neutral.gray700} style={[styles.docPara, isRTL && styles.rtlText]}>
                    {isRTL
                      ? 'مايووش ديزاين ش.م.م — المملكة المغربية. البريد: contact@mayush.ma'
                      : 'Mayush Design SARL — Casablanca, Maroc. Contact: contact@mayush.ma / Site: www.mayush.ma. Utilisation de cookies fonctionnels.'}
                  </MayushText>
                </View>
              )}
            </ScrollView>

            <TouchableOpacity style={styles.confirmBtn} onPress={() => setActiveModalDoc(null)}>
              <MayushText variant="strongBody" color={colors.neutral.white}>
                {isRTL ? 'إغلاق' : 'Fermer'}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>

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
  sectionBlock: { gap: spacing.xs },
  sectionTitleText: { fontSize: 15, fontWeight: '700', color: colors.neutral.gray700, marginLeft: spacing.xs },
  card: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.sm,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, paddingVertical: spacing.sm, paddingHorizontal: spacing.xs },
  iconBox: { width: 36, height: 36, borderRadius: 10, backgroundColor: colors.neutral.gray100, alignItems: 'center', justifyContent: 'center' },
  textCol: { flex: 1 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300 },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  modalCard: { backgroundColor: colors.neutral.white, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: spacing.md, maxHeight: '80%', gap: spacing.md },
  modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  closeBtn: { padding: spacing.xs },
  modalScroll: { maxHeight: 350 },
  modalBody: { gap: spacing.md },
  docSec: { gap: spacing.xs },
  docPara: { fontSize: 14, lineHeight: 20 },
  confirmBtn: { backgroundColor: colors.brand.navy900, borderRadius: 12, paddingVertical: 14, alignItems: 'center' },
});
