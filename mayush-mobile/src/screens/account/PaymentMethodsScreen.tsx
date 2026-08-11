import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState, PaymentMethodFixture } from '../../commerce/accountPreferencesState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface PaymentMethodsScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onContinueToLanguage?: () => void;
  language?: 'fr' | 'ar' | 'en';
}

export const PaymentMethodsScreen: React.FC<PaymentMethodsScreenProps> = ({
  onNavigateTab,
  onBack,
  onContinueToLanguage,
  language = 'fr',
}) => {
  const isRTL = language === 'ar';
  const [methods, setMethods] = useState<PaymentMethodFixture[]>(accountPreferencesState.getPaymentMethods());
  const [selectedId, setSelectedId] = useState<string>(accountPreferencesState.getSelectedPaymentMethodId());

  useEffect(() => {
    const unsub = accountPreferencesState.subscribe(() => {
      setMethods(accountPreferencesState.getPaymentMethods());
      setSelectedId(accountPreferencesState.getSelectedPaymentMethodId());
    });
    return unsub;
  }, []);

  const handleSelect = (id: string) => {
    accountPreferencesState.setSelectedPaymentMethod(id);
  };

  const handleRemove = (id: string) => {
    accountPreferencesState.removePaymentMethod(id);
  };

  const copy = language === 'ar'
    ? {
        title: 'طرق الدفع',
        subtitle: 'إدارة وتحديد طرق الدفع المفضلة لديك.',
        defaultBadge: 'الافتراضي',
        remove: 'حذف',
        select: 'تحديد',
        secureText: 'معلومات الدفع آمنة ومشفّرة عبر CMI Maroc.',
        nextStep: 'تفضيلات اللغة والمنطقة',
      }
    : language === 'en'
    ? {
        title: 'Payment Methods',
        subtitle: 'Manage and select your preferred payment options.',
        defaultBadge: 'Default',
        remove: 'Remove',
        select: 'Select',
        secureText: 'Payment details are secure and encrypted via CMI Morocco.',
        nextStep: 'Language & Region Preferences',
      }
    : {
        title: 'Moyens de paiement',
        subtitle: 'Gérez et sélectionnez vos modes de paiement préférés.',
        defaultBadge: 'Par défaut',
        remove: 'Supprimer',
        select: 'Sélectionner',
        secureText: 'Informations de paiement sécurisées et cryptées via CMI Maroc.',
        nextStep: 'Préférences langue & région',
      };

  return (
    <View style={styles.screen}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rowReverse]}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.backButton}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {copy.title}
        </MayushText>
        <View style={styles.headerSpacer} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={[styles.titleRow, isRTL && styles.rowReverse]}>
          <View style={styles.flex1}>
            <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
              {copy.title}
            </MayushText>
            <MayushText variant="caption" color={colors.neutral.gray700} style={[styles.subtitle, isRTL && styles.rtlText]}>
              {copy.subtitle}
            </MayushText>
          </View>
          <View style={styles.iconCircle}>
            <MayushIcon name="credit-card" size={24} color={colors.brand.navy900} />
          </View>
        </View>

        {/* List of Payment Methods */}
        <View style={styles.methodsList}>
          {methods.map((method) => {
            const isSelected = method.id === selectedId;
            return (
              <View key={method.id} style={[styles.card, isSelected && styles.cardSelected]}>
                <View style={[styles.cardHeader, isRTL && styles.rowReverse]}>
                  <View style={styles.cardIconBox}>
                    <MayushIcon name={method.iconName} size={22} color={colors.brand.orange500} />
                  </View>
                  <View style={styles.flex1}>
                    <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                      {method.title}
                    </MayushText>
                    <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                      {method.subtitle}
                    </MayushText>
                  </View>
                  <TouchableOpacity accessibilityRole="button" onPress={() => handleSelect(method.id)}>
                    <View style={[styles.radio, isSelected && styles.radioSelected]}>
                      {isSelected && <View style={styles.radioInner} />}
                    </View>
                  </TouchableOpacity>
                </View>

                {method.isDefault && (
                  <View style={styles.defaultPill}>
                    <MayushIcon name="star" size={12} color={colors.brand.orange500} />
                    <MayushText variant="caption" color={colors.brand.orange500} style={styles.defaultText}>
                      {copy.defaultBadge}
                    </MayushText>
                  </View>
                )}

                <View style={styles.divider} />

                <View style={[styles.cardActions, isRTL && styles.rowReverse]}>
                  {!isSelected && (
                    <TouchableOpacity accessibilityRole="button" onPress={() => handleSelect(method.id)} style={styles.selectBtn}>
                      <MayushIcon name="check" size={14} color={colors.brand.navy900} />
                      <MayushText variant="button" color={colors.brand.navy900} style={styles.btnLabel}>
                        {copy.select}
                      </MayushText>
                    </TouchableOpacity>
                  )}
                  {methods.length > 1 && (
                    <TouchableOpacity accessibilityRole="button" onPress={() => handleRemove(method.id)} style={styles.removeBtn}>
                      <MayushIcon name="trash-2" size={14} color={colors.semantic.error} />
                      <MayushText variant="button" color={colors.semantic.error} style={styles.btnLabel}>
                        {copy.remove}
                      </MayushText>
                    </TouchableOpacity>
                  )}
                </View>
              </View>
            );
          })}
        </View>

        {/* Security Assurance */}
        <View style={[styles.securityNotice, isRTL && styles.rowReverse]}>
          <MayushIcon name="shield" size={16} color={colors.brand.orange500} />
          <MayushText variant="caption" color={colors.neutral.gray700} style={styles.securityText}>
            {copy.secureText}
          </MayushText>
        </View>

        {/* Connection to Next Figma Screen 309:769 */}
        {onContinueToLanguage && (
          <TouchableOpacity accessibilityRole="button" onPress={onContinueToLanguage} style={[styles.nextButton, isRTL && styles.rowReverse]}>
            <MayushText variant="button" color={colors.surface.white}>
              {copy.nextStep}
            </MayushText>
            <MayushIcon name={isRTL ? 'arrow-left' : 'arrow-right'} size={18} color={colors.surface.white} />
          </TouchableOpacity>
        )}
      </ScrollView>

      <BottomTabBar activeTab="account" onTabPress={onNavigateTab} />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white },
  header: {
    height: 56, paddingHorizontal: spacing.md, flexDirection: 'row', alignItems: 'center',
    justifyContent: 'space-between', borderBottomWidth: 1, borderBottomColor: '#E7DED3',
  },
  backButton: { padding: 4 },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  headerSpacer: { width: 32 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl' },
  flex1: { flex: 1 },
  content: { padding: spacing.md, paddingBottom: 100, gap: 14 },
  titleRow: { flexDirection: 'row', gap: 12, alignItems: 'flex-start', marginTop: 4 },
  title: { fontSize: 22, lineHeight: 28 },
  subtitle: { fontSize: 12, marginTop: 2 },
  iconCircle: {
    width: 44, height: 44, borderRadius: 12, alignItems: 'center',
    justifyContent: 'center', backgroundColor: '#FFF7EF',
  },
  methodsList: { gap: 12, marginTop: 4 },
  card: {
    borderWidth: 1, borderColor: '#F0E3D7', borderRadius: 14, padding: 14,
    backgroundColor: colors.surface.white, shadowColor: colors.brand.navy900,
    shadowOpacity: 0.04, shadowRadius: 6, shadowOffset: { width: 0, height: 2 }, elevation: 2,
  },
  cardSelected: { borderColor: colors.brand.orange500 },
  cardHeader: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  cardIconBox: {
    width: 42, height: 42, borderRadius: 10, backgroundColor: '#FFF6E8',
    alignItems: 'center', justifyContent: 'center',
  },
  radio: {
    width: 20, height: 20, borderRadius: 10, borderWidth: 1.5,
    borderColor: colors.brand.navy900, alignItems: 'center', justifyContent: 'center',
  },
  radioSelected: { borderColor: colors.brand.orange500 },
  radioInner: { width: 11, height: 11, borderRadius: 6, backgroundColor: colors.brand.orange500 },
  defaultPill: {
    alignSelf: 'flex-start', marginTop: 8, paddingHorizontal: 8, paddingVertical: 2,
    borderRadius: 6, backgroundColor: '#FFF6E8', flexDirection: 'row', alignItems: 'center', gap: 4,
  },
  defaultText: { fontSize: 10, fontWeight: '700' },
  divider: { height: 1, backgroundColor: '#F0E3D7', marginVertical: 10 },
  cardActions: { flexDirection: 'row', gap: 10 },
  selectBtn: {
    height: 32, paddingHorizontal: 12, borderRadius: 6, borderWidth: 1,
    borderColor: colors.brand.navy900, flexDirection: 'row', alignItems: 'center', gap: 6,
  },
  removeBtn: {
    height: 32, paddingHorizontal: 12, borderRadius: 6, borderWidth: 1,
    borderColor: colors.semantic.error, flexDirection: 'row', alignItems: 'center', gap: 6,
    backgroundColor: '#FFEEEC',
  },
  btnLabel: { fontSize: 11 },
  securityNotice: {
    flexDirection: 'row', alignItems: 'center', gap: 8, marginTop: 8, padding: 12,
    borderRadius: 10, backgroundColor: '#FFFCF7', borderWidth: 1, borderColor: '#F0E3D7',
  },
  securityText: { flex: 1, fontSize: 11, lineHeight: 15 },
  nextButton: {
    height: 48, borderRadius: 14, backgroundColor: colors.brand.orange500,
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, marginTop: 10,
  },
});
