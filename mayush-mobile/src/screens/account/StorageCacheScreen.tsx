import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { cacheState } from '../../commerce/cacheState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface StorageCacheScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onOpenClearCacheModal?: () => void;
}

export const StorageCacheScreen: React.FC<StorageCacheScreenProps> = ({
  onNavigateTab,
  onBack,
  onOpenClearCacheModal,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [metrics, setMetrics] = useState(cacheState.getMetrics());

  useEffect(() => {
    return cacheState.subscribe(() => {
      setMetrics(cacheState.getMetrics());
    });
  }, []);

  const formattedSize = cacheState.getFormattedCacheSize();

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'التخزين والذاكرة المؤقتة' : 'Gestion du stockage & cache'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Storage Summary Card */}
        <View style={styles.summaryCard}>
          <View style={styles.iconBox}>
            <MayushIcon name="sliders" size={28} color={colors.brand.orange500} />
          </View>
          <MayushText variant="cardTitle" color={colors.brand.navy900} style={styles.summaryTitle}>
            {formattedSize}
          </MayushText>
          <MayushText variant="smallBody" color={colors.neutral.gray500}>
            {isRTL ? 'إجمالي الذاكرة المؤقتة المستخدمة' : 'Taille totale du cache d\'images et médias'}
          </MayushText>

          {/* Progress Bar */}
          <View style={styles.progressTrack}>
            <View style={[styles.progressFill, { width: metrics.cacheSizeBytes > 0 ? '75%' : '0%' }]} />
          </View>
        </View>

        {/* Breakdown Card */}
        <View style={styles.card}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.cardLabel, isRTL && styles.rtlText]}>
            {isRTL ? 'تفاصيل التخزين' : 'Détail de l\'espace occupé'}
          </MayushText>

          <View style={[styles.itemRow, isRTL && styles.rtlRow]}>
            <MayushIcon name="bookmark" size={18} color={colors.brand.navy900} />
            <View style={styles.textCol}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {isRTL ? 'صور المنتجات والمعاينة' : 'Cache d\'images & aperçus'}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {metrics.cachedImageCount} {isRTL ? 'ملف مخزن مؤقتاً' : 'fichiers temporaires'}
              </MayushText>
            </View>
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {formattedSize}
            </MayushText>
          </View>

          <View style={styles.divider} />

          <View style={[styles.itemRow, isRTL && styles.rtlRow]}>
            <MayushIcon name="sliders" size={18} color={colors.brand.navy900} />
            <View style={styles.textCol}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {isRTL ? 'بيانات التطبيق والتفضيلات' : 'Préférences & données locales'}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {isRTL ? 'السلة، المفضلة وإعدادات الحساب (دائمة)' : 'Panier, favoris & compte (durable)'}
              </MayushText>
            </View>
            <MayushText variant="strongBody" color={colors.semantic.success}>
              4 Mo
            </MayushText>
          </View>
        </View>

        {/* Clear Cache CTA */}
        <TouchableOpacity
          style={[styles.clearButton, isRTL && styles.rtlRow]}
          onPress={onOpenClearCacheModal}
          activeOpacity={0.85}
        >
          <MayushIcon name="trash-2" size={18} color={colors.semantic.error} />
          <MayushText variant="button" color={colors.semantic.error}>
            {isRTL ? 'تفراغ الذاكرة المؤقتة (Vider le cache)' : 'Vider le cache'}
          </MayushText>
        </TouchableOpacity>

        {metrics.lastClearedAt && (
          <MayushText variant="caption" color={colors.neutral.gray500} align="center">
            {isRTL ? 'آخر تفريغ للذاكرة: ' : 'Dernier nettoyage : '}
            {new Date(metrics.lastClearedAt).toLocaleTimeString()}
          </MayushText>
        )}
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
  summaryCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.lg,
    borderWidth: 1, borderColor: colors.neutral.gray300, alignItems: 'center',
  },
  iconBox: {
    width: 56, height: 56, borderRadius: 16, backgroundColor: 'rgba(217,116,52,0.1)',
    alignItems: 'center', justifyContent: 'center', marginBottom: spacing.xs,
  },
  summaryTitle: { fontSize: 26, fontWeight: '800', marginTop: spacing.xs },
  progressTrack: {
    width: '100%', height: 8, borderRadius: 4, backgroundColor: colors.neutral.gray300,
    marginTop: spacing.md, overflow: 'hidden',
  },
  progressFill: { height: '100%', backgroundColor: colors.brand.orange500, borderRadius: 4 },
  card: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300, gap: spacing.xs,
  },
  cardLabel: { fontSize: 16, fontWeight: '700', marginBottom: spacing.xs },
  itemRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, paddingVertical: 6 },
  textCol: { flex: 1 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300 },
  clearButton: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: colors.semantic.error,
  },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
