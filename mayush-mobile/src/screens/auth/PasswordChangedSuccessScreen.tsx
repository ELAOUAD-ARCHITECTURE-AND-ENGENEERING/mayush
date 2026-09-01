import React from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

export interface PasswordChangedSuccessScreenProps {
  onBackToLogin?: () => void;
}

export const PasswordChangedSuccessScreen: React.FC<PasswordChangedSuccessScreenProps> = ({
  onBackToLogin,
}) => {
  const { isRTL, language } = useTheme();

  const copy =
    language === 'ar'
      ? {
          title: 'تم تغيير كلمة السر بنجاح!',
          subtitle: 'تم تحديث كلمة السر الخاصة بك بنجاح. يمكنك الآن تسجيل الدخول باستخدام كلمة السر الجديدة.',
          loginCTA: 'تسجيل الدخول الآن',
        }
      : {
          title: 'Mot de passe modifié avec succès !',
          subtitle:
            'Votre mot de passe a été mis à jour avec succès. Vous pouvez maintenant vous connecter à votre compte Mayush.',
          loginCTA: 'Se connecter maintenant',
        };

  return (
    <View style={styles.screen} accessibilityLabel={copy.title}>
      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        <View style={[styles.header, isRTL && styles.rowReverse]}>
          <View style={styles.headerSpacer} />
          <MayushLogo width={150} height={45} />
          <View style={styles.headerSpacer} />
        </View>

        <View style={styles.badgeContainer}>
          <View style={styles.badgeCircle}>
            <MayushIcon name="check" size={36} color={colors.semantic.success} />
          </View>
        </View>

        <MayushText variant="display" color={colors.brand.navy900} align="center" style={styles.title}>
          {copy.title}
        </MayushText>
        <MayushText variant="body" color={colors.neutral.gray700} align="center" style={styles.subtitle}>
          {copy.subtitle}
        </MayushText>

        <View style={styles.card}>
          <PrimaryButton label={copy.loginCTA} onPress={onBackToLogin || (() => undefined)} />
        </View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFF8F0' },
  scrollContent: { paddingHorizontal: 22, paddingBottom: 36, paddingTop: 20 },
  rowReverse: { flexDirection: 'row-reverse' },
  header: {
    height: 56,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  headerSpacer: { width: 40 },
  badgeContainer: { alignItems: 'center', marginTop: 32 },
  badgeCircle: {
    width: 72,
    height: 72,
    borderRadius: 36,
    backgroundColor: '#E8F5E9',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: '#C8E6C9',
  },
  title: { marginTop: 22, fontSize: 26, lineHeight: 32 },
  subtitle: { marginTop: 12, marginHorizontal: 12, lineHeight: 24 },
  card: {
    marginTop: 32,
    padding: 20,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    shadowColor: colors.brand.navy900,
    shadowOpacity: 0.06,
    shadowRadius: 12,
    shadowOffset: { width: 0, height: 4 },
    elevation: 3,
  },
});
