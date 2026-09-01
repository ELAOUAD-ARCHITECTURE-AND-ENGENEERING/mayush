import React from 'react';
import { View, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { systemState } from '../../commerce/systemState';

interface SettingsErrorLoadingStateScreenProps {
  onRetry?: () => void;
  onGoHome?: () => void;
  onNavigatePrototypeNext?: () => void;
}

export const SettingsErrorLoadingStateScreen: React.FC<SettingsErrorLoadingStateScreenProps> = ({
  onRetry,
  onGoHome,
  onNavigatePrototypeNext,
}) => {
  const currentLanguage = accountPreferencesState.getSelectedLanguage();
  const isRTL = currentLanguage === 'ar';

  const handleRetry = () => {
    systemState.retrySettingsLoad();
    if (onRetry) {
      onRetry();
    }
  };

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <MayushText variant="cardTitle" color={colors.brand.navy900}>
          {isRTL ? 'الإعدادات' : 'Paramètres'}
        </MayushText>
        {onNavigatePrototypeNext ? (
          <TouchableOpacity onPress={onNavigatePrototypeNext} style={styles.protoDevBtn}>
            <MayushText variant="caption" color={colors.neutral.gray500}>
              Next Skeleton (309:827) →
            </MayushText>
          </TouchableOpacity>
        ) : null}
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Error Artwork Box */}
        <View style={styles.artworkContainer}>
          <View style={styles.artworkOuterBox}>
            <View style={styles.gearCircleBg}>
              <MayushIcon name="sliders" size={42} color={colors.neutral.white} />
            </View>
            <View style={styles.alertBadge}>
              <MayushIcon name="alert-triangle" size={16} color={colors.neutral.white} />
            </View>
          </View>
        </View>

        {/* Header Text */}
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
          {isRTL ? 'تعذر تحميل الإعدادات' : 'Impossible de charger les paramètres'}
        </MayushText>
        <MayushText variant="body" color={colors.neutral.gray700} style={[styles.subtitle, isRTL && styles.rtlText]}>
          {isRTL
            ? 'حدث خطأ فني أثناء استرجاع تفضيلات حسابك. تفضيلاتك وحسابك محفوظة بأمان.'
            : 'Un problème technique est survenu lors du chargement de vos préférences. Vos informations de compte restent sécurisées.'}
        </MayushText>

        {/* Safety Note Card */}
        <View style={styles.safetyCard}>
          <View style={styles.safetyIconCircle}>
            <MayushIcon name="shield-check" size={20} color={colors.brand.orange500} />
          </View>
          <View style={styles.safetyTextCol}>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={[styles.safetyTitle, isRTL && styles.rtlText]}>
              {isRTL ? 'بياناتك محفوظة' : 'Vos données restent conservées'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray700} style={[styles.safetyDesc, isRTL && styles.rtlText]}>
              {isRTL
                ? 'إعادة المحاولة لن تؤدي إلى مسح معلوماتك أو إعادة ضبط تفضيلاتك.'
                : 'La réessai ne supprimera aucune de vos données sauvegardées ni vos préférences enregistrées.'}
            </MayushText>
          </View>
        </View>

        {/* Action CTAs */}
        <View style={styles.actionsContainer}>
          <TouchableOpacity onPress={handleRetry} activeOpacity={0.8} style={styles.primaryBtn}>
            <MayushIcon name="refresh-cw" size={20} color={colors.neutral.white} />
            <MayushText variant="button" color={colors.neutral.white}>
              {isRTL ? 'إعادة المحاولة' : 'Réessayer'}
            </MayushText>
          </TouchableOpacity>

          <TouchableOpacity onPress={onGoHome} activeOpacity={0.8} style={styles.secondaryBtn}>
            <MayushIcon name="home" size={20} color={colors.brand.navy900} />
            <MayushText variant="button" color={colors.brand.navy900}>
              {isRTL ? 'العودة إلى الرئيسية' : 'Retour à l’accueil'}
            </MayushText>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.neutral.gray100 },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.md,
    paddingTop: 48,
    paddingBottom: spacing.sm,
    backgroundColor: colors.neutral.white,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(0,0,0,0.05)',
  },
  protoDevBtn: { padding: spacing.xs },
  scrollContent: { padding: spacing.md, alignItems: 'center' },
  artworkContainer: { marginVertical: spacing.lg, alignItems: 'center' },
  artworkOuterBox: {
    width: 140,
    height: 140,
    borderRadius: 24,
    backgroundColor: colors.neutral.white,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
    borderWidth: 1,
    borderColor: 'rgba(0,0,0,0.05)',
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 8,
  },
  gearCircleBg: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: colors.brand.navy900,
    alignItems: 'center',
    justifyContent: 'center',
  },
  alertBadge: {
    position: 'absolute',
    bottom: 16,
    right: 16,
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: colors.brand.orange500,
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: { textAlign: 'center', marginBottom: spacing.xs, paddingHorizontal: spacing.sm },
  subtitle: { textAlign: 'center', lineHeight: 20, paddingHorizontal: spacing.md, marginBottom: spacing.lg },
  safetyCard: {
    width: '100%',
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: spacing.sm,
    backgroundColor: colors.neutral.white,
    borderRadius: 16,
    padding: spacing.md,
    marginBottom: spacing.xl,
    borderWidth: 1,
    borderColor: 'rgba(0,0,0,0.05)',
  },
  safetyIconCircle: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: 'rgba(232,125,62,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  safetyTextCol: { flex: 1 },
  safetyTitle: { marginBottom: 2 },
  safetyDesc: { lineHeight: 18 },
  actionsContainer: { width: '100%', gap: spacing.sm },
  primaryBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
    backgroundColor: colors.brand.orange500,
    borderRadius: 14,
    paddingVertical: 14,
  },
  secondaryBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
    backgroundColor: colors.neutral.white,
    borderRadius: 14,
    paddingVertical: 14,
    borderWidth: 1,
    borderColor: colors.brand.navy900,
  },
  rtlText: { textAlign: 'right' },
});
