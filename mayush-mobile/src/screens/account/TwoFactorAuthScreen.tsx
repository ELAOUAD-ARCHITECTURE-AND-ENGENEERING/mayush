import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, Switch, TouchableOpacity, View } from 'react-native';
import { authState } from '../../commerce/authState';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface TwoFactorAuthScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  language?: 'fr' | 'ar';
}

export const TwoFactorAuthScreen: React.FC<TwoFactorAuthScreenProps> = ({
  onNavigateTab,
  onBack,
  language = 'fr',
}) => {
  const isRTL = language === 'ar';
  const [isEnabled, setIsEnabled] = useState(authState.isTwoFactorEnabled());
  const [phone] = useState(authState.getUser()?.phone || '+212 6 61 99 88 77');

  useEffect(() => {
    const unsub = authState.subscribe(() => {
      setIsEnabled(authState.isTwoFactorEnabled());
    });
    return unsub;
  }, []);

  const handleToggle = (value: boolean) => {
    setIsEnabled(value);
    authState.setTwoFactorEnabled(value);
  };

  return (
    <View style={styles.screen}>
      {/* Top Header */}
      <View style={[styles.header, isRTL && styles.rowReverse]}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.backButton}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'المصادقة الثنائية 2FA' : 'Validation en 2 étapes'}
        </MayushText>
        <View style={styles.headerRightPlaceholder} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        {/* Banner / Card */}
        <View style={styles.bannerCard}>
          <View style={styles.iconCircle}>
            <MayushIcon name="shield" size={28} color={colors.brand.orange500} />
          </View>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
            {language === 'ar' ? 'حماية إضافية لحسابك' : 'Protégez votre compte Mayush'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray700} style={[styles.bannerDescription, isRTL && styles.rtlText]}>
            {language === 'ar'
              ? 'عند تفعيل المصادقة الثنائية، ستحتاج إلى إدخال رمز إضافي يتم إرساله عبر SMS في كل مرة تسجل فيها الدخول من جهاز جديد.'
              : 'À chaque nouvelle connexion, un code de sécurité unique vous sera envoyé par SMS pour vérifier votre identité.'}
          </MayushText>
        </View>

        {/* Toggle Controls Card */}
        <View style={styles.controlCard}>
          <View style={[styles.toggleRow, isRTL && styles.rowReverse]}>
            <View style={styles.toggleTextGroup}>
              <MayushText variant="cardTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'تفعيل المصادقة عبر SMS' : 'Activer la validation par SMS'}
              </MayushText>
              <MayushText variant="body" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {isEnabled
                  ? language === 'ar'
                    ? 'الحساب محمي بـ 2FA حالياً'
                    : 'Le service 2FA est actif'
                  : language === 'ar'
                  ? 'الميزة متوقفة حالياً'
                  : 'Le service 2FA est désactivé'}
              </MayushText>
            </View>
            <Switch
              value={isEnabled}
              onValueChange={handleToggle}
              trackColor={{ false: '#E7DED3', true: colors.brand.orange500 }}
              thumbColor={colors.surface.white}
            />
          </View>
        </View>

        {/* Phone Info Card */}
        <View style={styles.phoneCard}>
          <View style={[styles.phoneRow, isRTL && styles.rowReverse]}>
            <MayushIcon name="user" size={22} color={colors.brand.navy900} />
            <View style={styles.phoneTextGroup}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'رقم الهاتف المسجل' : 'Numéro de téléphone associé'}
              </MayushText>
              <MayushText variant="cardTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {phone}
              </MayushText>
            </View>
            <MayushIcon name="check-circle" size={20} color={colors.semantic.success} />
          </View>
        </View>

        {/* Primary Action Button */}
        <PrimaryButton
          label={
            isEnabled
              ? language === 'ar'
                ? 'تعطيل المصادقة الثنائية'
                : 'Désactiver la validation 2FA'
              : language === 'ar'
              ? 'تفعيل المصادقة الثنائية'
              : 'Activer la validation 2FA'
          }
          onPress={() => handleToggle(!isEnabled)}
          style={styles.actionButton}
        />
      </ScrollView>

      <BottomTabBar activeTab="account" onTabPress={onNavigateTab} />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.creamLight },
  header: {
    height: 56,
    paddingHorizontal: spacing.md,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: colors.surface.white,
    borderBottomWidth: 1,
    borderBottomColor: '#E7DED3',
  },
  backButton: { padding: 4 },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  headerRightPlaceholder: { width: 32 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl' },
  content: { padding: spacing.md, paddingBottom: 100, gap: 14 },
  bannerCard: {
    padding: 20,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
    alignItems: 'center',
    textAlign: 'center',
    gap: 10,
  },
  iconCircle: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: colors.surface.cream,
    alignItems: 'center',
    justifyContent: 'center',
  },
  bannerDescription: { fontSize: 13, lineHeight: 20, textAlign: 'center' },
  controlCard: {
    padding: 16,
    borderRadius: 16,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
  },
  toggleRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 12 },
  toggleTextGroup: { flex: 1, gap: 2 },
  phoneCard: {
    padding: 16,
    borderRadius: 16,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
  },
  phoneRow: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  phoneTextGroup: { flex: 1, gap: 2 },
  actionButton: { marginTop: 10 },
});
