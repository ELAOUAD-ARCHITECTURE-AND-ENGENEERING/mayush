import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { cacheState, CacheLayerStatus, CacheMetrics } from '../../commerce/cacheState';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface StorageCacheScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onOpenClearCacheModal?: () => void;
}

function layerStatusIcon(status: CacheLayerStatus): { name: MayushIconName; color: string } {
  switch (status) {
    case 'cleared':
      return { name: 'check-circle', color: colors.semantic.success };
    case 'clearing':
      return { name: 'refresh-cw', color: colors.brand.orange500 };
    case 'failed':
      return { name: 'alert-circle', color: colors.semantic.error };
    default:
      return { name: 'circle', color: colors.neutral.gray500 };
  }
}

export const StorageCacheScreen: React.FC<StorageCacheScreenProps> = ({
  onNavigateTab,
  onBack,
  onOpenClearCacheModal,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [metrics, setMetrics] = useState<CacheMetrics>(cacheState.getMetrics());

  useEffect(() => {
    return cacheState.subscribe(() => {
      setMetrics(cacheState.getMetrics());
    });
  }, []);

  const handleRetry = () => {
    cacheState.retryClear();
  };

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
        {/* Summary Card */}
        <View style={styles.summaryCard}>
          <View style={styles.iconBox}>
            <MayushIcon name="sliders" size={28} color={colors.brand.orange500} />
          </View>
          <MayushText variant="cardTitle" color={colors.brand.navy900} style={styles.summaryTitle}>
            {isRTL ? 'إدارة الذاكرة المؤقتة' : 'Gestion du cache'}
          </MayushText>
          <MayushText variant="smallBody" color={colors.neutral.gray500}>
            {metrics.lastClearedAt
              ? `${isRTL ? 'آخر تفريغ: ' : 'Dernier nettoyage : '}${new Date(metrics.lastClearedAt).toLocaleDateString()} ${new Date(metrics.lastClearedAt).toLocaleTimeString()}`
              : isRTL
                ? 'لم يتم تفريغ الذاكرة المؤقتة بعد'
                : 'Cache jamais vidé'}
          </MayushText>

          {/* Progress Bar — visible during clearing */}
          {metrics.isClearing && (
            <View style={styles.progressTrack}>
              <View style={[styles.progressFill, { width: `${Math.round(metrics.clearProgress * 100)}%` }]} />
            </View>
          )}
        </View>

        {/* Layer Status Card */}
        <View style={styles.card}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.cardLabel, isRTL && styles.rtlText]}>
            {isRTL ? 'طبقات الذاكرة المؤقتة' : 'Couches du cache'}
          </MayushText>

          {metrics.layers.map((layer, index) => {
            const icon = layerStatusIcon(layer.status);
            return (
              <React.Fragment key={layer.id}>
                {index > 0 && <View style={styles.divider} />}
                <View style={[styles.itemRow, isRTL && styles.rtlRow]}>
                  <MayushIcon name={icon.name} size={18} color={icon.color} />
                  <View style={styles.textCol}>
                    <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                      {isRTL ? layer.labelAr : layer.label}
                    </MayushText>
                  </View>
                  <MayushText variant="smallBody" color={icon.color}>
                    {layer.status === 'idle'
                      ? (isRTL ? 'جاهز' : 'Prêt')
                      : layer.status === 'clearing'
                        ? (isRTL ? 'جاري...' : 'En cours...')
                        : layer.status === 'cleared'
                          ? (isRTL ? 'تم' : 'Vidé')
                          : (isRTL ? 'فشل' : 'Échec')}
                  </MayushText>
                </View>
              </React.Fragment>
            );
          })}
        </View>

        {/* Durable Data Info */}
        <View style={styles.card}>
          <View style={[styles.itemRow, isRTL && styles.rtlRow]}>
            <MayushIcon name="shield" size={18} color={colors.semantic.success} />
            <View style={styles.textCol}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {isRTL ? 'بيانات محمية' : 'Données protégées'}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {isRTL ? 'السلة، المفضلة، العناوين والحساب (لا يتم حذفها)' : 'Panier, favoris, adresses & compte (jamais supprimés)'}
              </MayushText>
            </View>
          </View>
        </View>

        {/* Clear Cache CTA or Retry */}
        {metrics.failedLayer ? (
          <TouchableOpacity
            style={[styles.retryButton, isRTL && styles.rtlRow]}
            onPress={handleRetry}
            activeOpacity={0.85}
          >
            <MayushIcon name="refresh-cw" size={18} color={colors.brand.orange500} />
            <MayushText variant="button" color={colors.brand.orange500}>
              {isRTL ? 'إعادة المحاولة' : 'Réessayer'}
            </MayushText>
          </TouchableOpacity>
        ) : (
          <TouchableOpacity
            style={[styles.clearButton, isRTL && styles.rtlRow]}
            onPress={onOpenClearCacheModal}
            activeOpacity={0.85}
            disabled={metrics.isClearing}
          >
            <MayushIcon name="trash-2" size={18} color={metrics.isClearing ? colors.neutral.gray500 : colors.semantic.error} />
            <MayushText variant="button" color={metrics.isClearing ? colors.neutral.gray500 : colors.semantic.error}>
              {isRTL ? 'تفراغ الذاكرة المؤقتة (Vider le cache)' : 'Vider le cache'}
            </MayushText>
          </TouchableOpacity>
        )}
      </ScrollView>
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
  summaryTitle: { fontSize: 22, fontWeight: '800', marginTop: spacing.xs },
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
  retryButton: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: colors.brand.orange500,
  },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
