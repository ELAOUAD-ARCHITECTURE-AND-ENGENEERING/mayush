/**
 * LoginErrorScreen (Figma 08-Login Error Screen)
 * Pixel-accurate rebuild matching mayush-mobile-design/08-account/08-Login Error Screen.png
 */

import React from 'react';
import {
  Image,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  useWindowDimensions,
  View,
} from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { fontFamilies } from '../../design-system/tokens/typography';

const HERO_ART = require('../../../assets/reference-art/login-error-hero-art.png');

export interface LoginErrorScreenProps {
  errorMessage?: string;
  onRetry: () => void;
  onForgotPassword: () => void;
  onBack: () => void;
  onSupport?: () => void;
}

export const LoginErrorScreen: React.FC<LoginErrorScreenProps> = ({
  errorMessage,
  onRetry,
  onForgotPassword,
  onBack,
  onSupport,
}) => {
  const { isRTL, language } = useTheme();
  const { width: viewportWidth } = useWindowDimensions();
  const artWidth = Math.min(viewportWidth * 0.76, 320);
  const artHeight = artWidth * 615 / 841;

  const defaultErrorText = language === 'ar'
    ? 'البريد الإلكتروني، رقم الهاتف أو كلمة المرور غير صحيحة. تحقق من بياناتك ثم أعد المحاولة.'
    : 'L’e-mail, le numéro marocain ou le mot de passe saisi est incorrect. Vérifiez vos identifiants puis réessayez.';

  const displaySubtitle = errorMessage || defaultErrorText;

  return (
    <View style={styles.screen} accessibilityLabel={language === 'ar' ? 'تعذر تسجيل الدخول' : 'Connexion impossible'}>
      {/* Top Header Navigation */}
      <View style={[styles.headerRow, isRTL && styles.rowReverse]}>
        <TouchableOpacity
          onPress={onBack}
          style={styles.headerCircleBtn}
          activeOpacity={0.8}
          accessibilityRole="button"
          accessibilityLabel={language === 'ar' ? 'رجوع' : 'Retour'}
        >
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={20} color={colors.brand.navy900} />
        </TouchableOpacity>

        <View style={styles.logoContainer}>
          <MayushLogo width={140} height={42} />
        </View>

        <TouchableOpacity
          onPress={onSupport || onBack}
          style={styles.headerCircleBtn}
          activeOpacity={0.8}
          accessibilityRole="button"
          accessibilityLabel={language === 'ar' ? 'المساعدة والدعم' : 'Aide et support'}
        >
          <MayushIcon name="headphones" size={20} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {/* 3D Hero Art */}
        <View style={styles.artWrapper}>
          <Image
            source={HERO_ART}
            resizeMode="contain"
            style={{ width: artWidth, height: artHeight }}
            accessible={false}
          />
        </View>

        {/* Title */}
        <MayushText
          variant="pageTitle"
          color={colors.brand.navy900}
          align="center"
          style={styles.title}
        >
          {language === 'ar' ? 'تعذر تسجيل الدخول' : 'Connexion impossible'}
        </MayushText>

        {/* Subtitle / Description */}
        <MayushText
          variant="body"
          color={colors.neutral.gray700}
          align="center"
          style={styles.subtitle}
        >
          {displaySubtitle}
        </MayushText>

        {/* Info Card: Accepted Formats */}
        <View style={[styles.infoBanner, isRTL && styles.rowReverse]}>
          <View style={styles.infoIconCircle}>
            <MayushIcon name="info" size={18} color={colors.brand.orange500} />
          </View>
          <MayushText variant="smallBody" color={colors.neutral.gray700} style={styles.infoText}>
            {language === 'ar'
              ? 'الصيغ المقبولة: بريد إلكتروني أو رقم مغربي (+212)'
              : 'Formats acceptés : e-mail ou numéro marocain (+212)'}
          </MayushText>
        </View>

        {/* Primary CTA: Réessayer */}
        <TouchableOpacity
          style={styles.primaryBtn}
          onPress={onRetry}
          activeOpacity={0.86}
          accessibilityRole="button"
          accessibilityLabel={language === 'ar' ? 'إعادة المحاولة' : 'Réessayer'}
        >
          <MayushText variant="button" color={colors.surface.white} style={styles.primaryBtnText}>
            {language === 'ar' ? 'إعادة المحاولة' : 'Réessayer'}
          </MayushText>
        </TouchableOpacity>

        {/* Divider with Lock Icon */}
        <View style={styles.dividerRow}>
          <View style={styles.dividerLine} />
          <View style={styles.lockBadge}>
            <MayushIcon name="lock" size={13} color="#C4B4A0" />
          </View>
          <View style={styles.dividerLine} />
        </View>

        {/* Secondary Action: Mot de passe oublié ? */}
        <TouchableOpacity
          onPress={onForgotPassword}
          style={styles.forgotBtn}
          activeOpacity={0.78}
          accessibilityRole="button"
          accessibilityLabel={language === 'ar' ? 'هل نسيت كلمة المرور؟' : 'Mot de passe oublié ?'}
        >
          <MayushText variant="strongBody" color={colors.brand.orange500} align="center" style={styles.forgotText}>
            {language === 'ar' ? 'هل نسيت كلمة المرور؟' : 'Mot de passe oublié ?'}
          </MayushText>
        </TouchableOpacity>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: {
    flex: 1,
    backgroundColor: '#FAF5ED',
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingTop: 54,
    paddingBottom: 12,
  },
  rowReverse: {
    flexDirection: 'row-reverse',
  },
  headerCircleBtn: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOpacity: 0.05,
    shadowRadius: 6,
    shadowOffset: { width: 0, height: 2 },
    elevation: 2,
  },
  logoContainer: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  scrollContent: {
    paddingHorizontal: 24,
    paddingBottom: 40,
    alignItems: 'center',
  },
  artWrapper: {
    marginTop: 18,
    marginBottom: 24,
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: {
    fontSize: 26,
    lineHeight: 32,
    fontFamily: fontFamilies.latin.bold,
    marginBottom: 12,
  },
  subtitle: {
    fontSize: 14,
    lineHeight: 22,
    maxWidth: 340,
    marginBottom: 26,
    fontFamily: fontFamilies.latin.regular,
  },
  infoBanner: {
    width: '100%',
    maxWidth: 380,
    backgroundColor: '#FFF2E2',
    borderRadius: 16,
    paddingHorizontal: 16,
    paddingVertical: 14,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 24,
    borderWidth: 1,
    borderColor: 'rgba(230, 112, 43, 0.12)',
  },
  infoIconCircle: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOpacity: 0.04,
    shadowRadius: 4,
    shadowOffset: { width: 0, height: 1 },
    elevation: 1,
  },
  infoText: {
    flex: 1,
    fontSize: 13,
    lineHeight: 18,
    fontFamily: fontFamilies.latin.medium,
  },
  primaryBtn: {
    width: '100%',
    maxWidth: 380,
    height: 52,
    borderRadius: 16,
    backgroundColor: '#E6702B',
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#D95518',
    shadowOpacity: 0.25,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 4 },
    elevation: 4,
    marginBottom: 22,
  },
  primaryBtnText: {
    fontSize: 16,
    fontFamily: fontFamilies.latin.semiBold,
  },
  dividerRow: {
    width: '100%',
    maxWidth: 380,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginVertical: 10,
  },
  dividerLine: {
    flex: 1,
    height: 1,
    backgroundColor: '#E6DCCE',
  },
  lockBadge: {
    paddingHorizontal: 10,
    backgroundColor: '#FAF5ED',
  },
  forgotBtn: {
    paddingVertical: 10,
    paddingHorizontal: 16,
  },
  forgotText: {
    fontSize: 14,
    fontFamily: fontFamilies.latin.semiBold,
  },
});
