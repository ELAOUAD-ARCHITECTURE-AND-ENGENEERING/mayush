import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { authState } from '../../commerce/authState';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

export interface MyInformationScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onEditProfile?: () => void;
  onChangeEmail?: () => void;
  onChangePhone?: () => void;
}

export const MyInformationScreen: React.FC<MyInformationScreenProps> = ({
  onNavigateTab,
  onBack,
  onEditProfile,
  onChangeEmail,
  onChangePhone,
}) => {
  const { isRTL, language } = useTheme();
  const [user, setUser] = useState(authState.getUser());

  useEffect(() => {
    return authState.subscribe(() => {
      setUser(authState.getUser());
    });
  }, []);

  const fullName = user?.fullName || 'Karim Benjelloun';
  const email = user?.email || user?.emailOrPhone || 'karim.benjelloun@example.ma';
  const phone = user?.phone || '+212 6 61 99 88 77';
  const city = user?.city || 'Casablanca';
  const birthDate = user?.birthDate || '15/06/1992';
  const genderLabel = user?.gender === 'f' ? 'Femme' : user?.gender === 'm' ? 'Homme' : 'Non spécifié';

  return (
    <View style={styles.screen} accessibilityLabel="Mes informations personnelles">
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.scrollContent}>
        {/* Header */}
        <View style={[styles.header, isRTL && styles.rowReverse]}>
          <TouchableOpacity
            accessibilityRole="button"
            accessibilityLabel="Retour"
            onPress={onBack}
            style={styles.backButton}
          >
            <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
          <MayushText variant="sectionTitle" color={colors.brand.navy900}>
            {language === 'ar' ? 'معلوماتي الشخصية' : 'Mes Informations'}
          </MayushText>
          <View style={styles.headerSpacer} />
        </View>

        {/* Info Card */}
        <View style={styles.card}>
          <View style={[styles.infoRow, isRTL && styles.rowReverse]}>
            <View style={styles.infoTextGroup}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'الاسم الكامل' : 'Nom complet'}
              </MayushText>
              <MayushText variant="body" color={colors.brand.navy900} style={[styles.infoValue, isRTL && styles.rtlText]}>
                {fullName}
              </MayushText>
            </View>
          </View>

          <View style={styles.divider} />

          <View style={[styles.infoRow, isRTL && styles.rowReverse]}>
            <View style={styles.infoTextGroup}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'البريد الإلكتروني' : 'Adresse e-mail'}
              </MayushText>
              <MayushText variant="body" color={colors.brand.navy900} style={[styles.infoValue, isRTL && styles.rtlText]}>
                {email}
              </MayushText>
            </View>
            <TouchableOpacity accessibilityRole="button" onPress={onChangeEmail} style={styles.actionBadge}>
              <MayushText variant="caption" color={colors.brand.orange500} style={styles.actionBadgeText}>
                {language === 'ar' ? 'تعديل' : 'Modifier'}
              </MayushText>
            </TouchableOpacity>
          </View>

          <View style={styles.divider} />

          <View style={[styles.infoRow, isRTL && styles.rowReverse]}>
            <View style={styles.infoTextGroup}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'رقم الهاتف' : 'Téléphone'}
              </MayushText>
              <MayushText variant="body" color={colors.brand.navy900} style={[styles.infoValue, isRTL && styles.rtlText]}>
                {phone}
              </MayushText>
            </View>
            <TouchableOpacity accessibilityRole="button" onPress={onChangePhone} style={styles.actionBadge}>
              <MayushText variant="caption" color={colors.brand.orange500} style={styles.actionBadgeText}>
                {language === 'ar' ? 'تعديل' : 'Modifier'}
              </MayushText>
            </TouchableOpacity>
          </View>

          <View style={styles.divider} />

          <View style={[styles.infoRow, isRTL && styles.rowReverse]}>
            <View style={styles.infoTextGroup}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'المدينة' : 'Ville'}
              </MayushText>
              <MayushText variant="body" color={colors.brand.navy900} style={[styles.infoValue, isRTL && styles.rtlText]}>
                {city}
              </MayushText>
            </View>
          </View>

          <View style={styles.divider} />

          <View style={[styles.infoRow, isRTL && styles.rowReverse]}>
            <View style={styles.infoTextGroup}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'تاريخ الميلاد' : 'Date de naissance'}
              </MayushText>
              <MayushText variant="body" color={colors.brand.navy900} style={[styles.infoValue, isRTL && styles.rtlText]}>
                {birthDate}
              </MayushText>
            </View>
          </View>

          <View style={styles.divider} />

          <View style={[styles.infoRow, isRTL && styles.rowReverse]}>
            <View style={styles.infoTextGroup}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'الجنس' : 'Genre'}
              </MayushText>
              <MayushText variant="body" color={colors.brand.navy900} style={[styles.infoValue, isRTL && styles.rtlText]}>
                {genderLabel}
              </MayushText>
            </View>
          </View>
        </View>

        {/* Edit Button */}
        <PrimaryButton
          label={language === 'ar' ? 'تعديل معلوماتي' : 'Modifier mes informations'}
          onPress={onEditProfile || (() => {})}
          style={styles.editButton}
        />
      </ScrollView>
      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FBF2EF' },
  scrollContent: { paddingHorizontal: 22, paddingBottom: 28, paddingTop: 12 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl' },
  header: { height: 56, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  backButton: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  headerSpacer: { width: 40 },
  card: {
    marginTop: 20,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
    paddingHorizontal: 18,
    paddingVertical: 8,
  },
  infoRow: {
    minHeight: 64,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  infoTextGroup: { flex: 1, gap: 4 },
  infoValue: { fontWeight: '600' },
  actionBadge: {
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 8,
    backgroundColor: '#FFF3E6',
  },
  actionBadgeText: { fontWeight: '700' },
  divider: { height: 1, backgroundColor: '#F0E8DF' },
  editButton: { marginTop: 24, minHeight: 52 },
});
