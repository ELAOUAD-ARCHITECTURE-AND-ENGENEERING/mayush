import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, Switch, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { appSettingsState } from '../../commerce/appSettingsState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface OfflineModeScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
}

export const OfflineModeScreen: React.FC<OfflineModeScreenProps> = ({
  onNavigateTab,
  onBack,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [offlineEnabled, setOfflineEnabled] = useState(appSettingsState.getOfflineMode());

  useEffect(() => {
    return appSettingsState.subscribe(() => {
      setOfflineEnabled(appSettingsState.getOfflineMode());
    });
  }, []);

  const availableOffline = [
    {
      icon: 'grid',
      titleFr: 'Consultation du catalogue en cache',
      titleAr: 'تصفح الكتالوج المخزن مؤقتاً',
      descFr: 'Les informations de votre compte et vos préférences locales restent consultables.',
      descAr: 'معلومات حسابك والتفضيلات المحلية تبقى متاحة.',
    },
    {
      icon: 'shopping-cart',
      titleFr: 'Aperçu du panier & favoris',
      titleAr: 'معاينة السلة والمفضلة',
      descFr: 'Vos articles enregistrés restent accessibles hors-ligne.',
      descAr: 'المنتجات المحفوظة في السلة والمفضلة تبقى متاحة.',
    },
    {
      icon: 'bookmark',
      titleFr: 'Adresses & coordonnées enregistrées',
      titleAr: 'العناوين والمعلومات المحفوظة',
      descFr: 'Vos adresses de livraison restent consultables.',
      descAr: 'عناوين التوصيل المحفوظة قابلة للمعاينة.',
    },
  ];

  const requiresConnection = [
    {
      icon: 'shield',
      titleFr: 'Validation de commande & Paiement CMI',
      titleAr: 'تأكيد الطلب والدفع الإلكتروني CMI',
      descFr: 'Connexion requise pour sécuriser les transactions.',
      descAr: 'يتطلب اتصالاً بالإنترنت لإتمام الدفع الآمن.',
    },
    {
      icon: 'clock',
      titleFr: 'Suivi de commande en temps réel',
      titleAr: 'متابعة حالة الطلبات المباشرة',
      descFr: 'Synchronisation automatique dès le rétablissement du réseau.',
      descAr: 'مزامنة حالة التوصيل فور الاتصال.',
    },
    {
      icon: 'user',
      titleFr: 'Création et mise à jour de compte',
      titleAr: 'إنشاء وتحديث بيانات الحساب',
      descFr: 'Nécessite la connexion aux serveurs Mayush.',
      descAr: 'يتطلب الاتصال بخوادم مايووش.',
    },
  ];

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'الوضع غير المتصل بالإنترنت' : 'Mode hors-ligne & limitations'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Banner Card */}
        <View style={[styles.bannerCard, offlineEnabled && styles.bannerCardActive]}>
          <View style={styles.bannerIconBox}>
            <MayushIcon name={offlineEnabled ? 'info' : 'grid'} size={24} color={colors.brand.orange500} />
          </View>
          <View style={styles.bannerTextCol}>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {offlineEnabled
                ? (isRTL ? 'وضع عدم الاتصال مفعل حالياً' : 'Mode hors-ligne activé (Simulé)')
                : (isRTL ? 'الاتصال بالشبكة نشط' : 'Connexion réseau active')}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {isRTL
                ? 'يمكنك تجربة واجهة التطبيق في حالة ضعف أو غياب التغطية.'
                : 'Permet de tester l\'expérience utilisateur en cas d\'absence de réseau.'}
            </MayushText>
          </View>
          <Switch
            value={offlineEnabled}
            onValueChange={() => appSettingsState.toggleOfflineMode()}
            trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
            thumbColor={colors.neutral.white}
          />
        </View>

        {/* Available Offline Features */}
        <View style={styles.card}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.cardLabel, isRTL && styles.rtlText]}>
            {isRTL ? 'ميزات متاحة بدون إنترنت' : 'Fonctionnalités disponibles hors-ligne'}
          </MayushText>

          {availableOffline.map((item, idx) => (
            <React.Fragment key={idx}>
              {idx > 0 && <View style={styles.divider} />}
              <View style={[styles.itemRow, isRTL && styles.rtlRow]}>
                <View style={styles.iconBoxSuccess}>
                  <MayushIcon name={item.icon as any} size={18} color={colors.semantic.success} />
                </View>
                <View style={styles.textCol}>
                  <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                    {isRTL ? item.titleAr : item.titleFr}
                  </MayushText>
                  <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                    {isRTL ? item.descAr : item.descFr}
                  </MayushText>
                </View>
              </View>
            </React.Fragment>
          ))}
        </View>

        {/* Requires Connection Features */}
        <View style={styles.card}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.cardLabel, isRTL && styles.rtlText]}>
            {isRTL ? 'ميزات تطلب اتصالاً بالشبكة' : 'Opérations nécessitant une connexion'}
          </MayushText>

          {requiresConnection.map((item, idx) => (
            <React.Fragment key={idx}>
              {idx > 0 && <View style={styles.divider} />}
              <View style={[styles.itemRow, isRTL && styles.rtlRow]}>
                <View style={styles.iconBoxWarn}>
                  <MayushIcon name={item.icon as any} size={18} color={colors.brand.orange500} />
                </View>
                <View style={styles.textCol}>
                  <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                    {isRTL ? item.titleAr : item.titleFr}
                  </MayushText>
                  <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                    {isRTL ? item.descAr : item.descFr}
                  </MayushText>
                </View>
              </View>
            </React.Fragment>
          ))}
        </View>
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
  bannerCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.sm,
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  bannerCardActive: { borderColor: colors.brand.orange500, backgroundColor: 'rgba(217,116,52,0.06)' },
  bannerIconBox: { width: 40, height: 40, borderRadius: 10, backgroundColor: 'rgba(217,116,52,0.1)', alignItems: 'center', justifyContent: 'center' },
  bannerTextCol: { flex: 1, paddingRight: spacing.xs },
  card: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300, gap: spacing.xs,
  },
  cardLabel: { fontSize: 16, fontWeight: '700', marginBottom: spacing.xs },
  itemRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, paddingVertical: 6 },
  iconBoxSuccess: { width: 34, height: 34, borderRadius: 9, backgroundColor: colors.semantic.successBackground, alignItems: 'center', justifyContent: 'center' },
  iconBoxWarn: { width: 34, height: 34, borderRadius: 9, backgroundColor: 'rgba(217,116,52,0.1)', alignItems: 'center', justifyContent: 'center' },
  textCol: { flex: 1 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300 },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
