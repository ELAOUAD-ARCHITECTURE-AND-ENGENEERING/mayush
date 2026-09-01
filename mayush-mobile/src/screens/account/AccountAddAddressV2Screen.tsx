import React, { useState } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  Switch,
  TouchableOpacity,
  View,
} from 'react-native';
import { AddressDraft, AddressDraftErrors, emptyAddressDraft, validateAddressDraft, createSavedAddress } from '../../commerce/checkoutState';
import { authState } from '../../commerce/authState';
import { TextField } from '../../design-system/components/forms/TextField';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface AccountAddAddressV2ScreenProps {
  onBack: () => void;
  onSaved: () => void;
  language?: 'fr' | 'ar';
}

const update = (draft: AddressDraft, key: keyof AddressDraft, value: string | boolean): AddressDraft => ({ ...draft, [key]: value });

/**
 * Figma node 309:764 — 08-add-address-form-v2-fr
 *
 * Detailed add-address form with all fields: name, phone, city, zone,
 * full address, apartment, postcode, delivery instructions, label, and
 * default toggle.
 */
export const AccountAddAddressV2Screen: React.FC<AccountAddAddressV2ScreenProps> = ({
  onBack,
  onSaved,
  language = 'fr',
}) => {
  const isRTL = language === 'ar';
  const textAlign = isRTL ? 'right' : 'left';
  const [draft, setDraft] = useState<AddressDraft>(emptyAddressDraft());
  const [errors, setErrors] = useState<AddressDraftErrors>({});

  const copy = language === 'ar'
    ? {
        title: 'أضف عنوانًا جديدًا',
        subtitle: 'أضف معلوماتك لتوصيل سريع وموثوق.',
        name: 'الاسم الكامل', phone: 'رقم الهاتف', city: 'المدينة',
        zone: 'منطقة التوصيل', address: 'العنوان', apartment: 'الشقة، الطابق...',
        postcode: 'الرمز البريدي', instructions: 'تعليمات التوصيل',
        label: 'تسمية العنوان', defaultLabel: 'اجعل هذا العنوان افتراضيًا',
        save: 'حفظ العنوان',
      }
    : {
        title: 'Ajouter une adresse',
        subtitle: 'Ajoutez vos informations pour une livraison rapide et fiable.',
        name: 'Nom complet', phone: 'Numéro de téléphone', city: 'Ville',
        zone: 'Zone de livraison', address: 'Adresse complète',
        apartment: 'Appartement, étage, bâtiment',
        postcode: 'Code postal', instructions: 'Instructions de livraison',
        label: 'Étiquette de l\'adresse', defaultLabel: 'Définir comme adresse par défaut',
        save: 'Enregistrer l\'adresse',
      };

  const handleSave = () => {
    const errs = validateAddressDraft(draft);
    setErrors(errs);
    if (Object.keys(errs).length > 0) return;
    const id = `address-${Date.now()}`;
    const saved = createSavedAddress(draft, id);
    authState.addAddress(saved);
    onSaved();
  };

  return (
    <KeyboardAvoidingView style={styles.screen} behavior={Platform.select({ ios: 'padding', default: undefined })}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rowReverse]}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.back}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'عنوان جديد' : 'Nouvelle adresse'}
        </MayushText>
        <View style={styles.back} />
      </View>

      <ScrollView keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, { textAlign }]}>
          {copy.title}
        </MayushText>
        <MayushText variant="caption" color={colors.neutral.gray700} style={[styles.subtitle, { textAlign }]}>
          {copy.subtitle}
        </MayushText>

        <FormField label={copy.name} icon="user" value={draft.name} error={errors.name}
          placeholder={language === 'ar' ? 'أدخل اسمك الكامل' : 'Entrez votre nom complet'}
          onChangeText={(v) => setDraft(update(draft, 'name', v))} />
        <FormField label={copy.phone} icon="phone" value={draft.phone} error={errors.phone}
          keyboardType="phone-pad" placeholder="+212 6 XX XX XX XX"
          onChangeText={(v) => setDraft(update(draft, 'phone', v))} />
        <FormField label={copy.city} icon="map-pin" value={draft.city} error={errors.city}
          placeholder={language === 'ar' ? 'اختر مدينتك' : 'Sélectionnez votre ville'}
          onChangeText={(v) => setDraft(update(draft, 'city', v))} />
        <FormField label={copy.zone} icon="map" value={draft.zone} error={errors.zone}
          placeholder={language === 'ar' ? 'اختر منطقة التوصيل' : 'Sélectionnez votre zone de livraison'}
          onChangeText={(v) => setDraft(update(draft, 'zone', v))} />
        <FormField label={copy.address} icon="home" value={draft.addressLine} error={errors.addressLine}
          placeholder={language === 'ar' ? 'رقم، شارع، حي...' : 'Numéro, rue, quartier…'}
          onChangeText={(v) => setDraft(update(draft, 'addressLine', v))} />
        <FormField label={copy.apartment} icon="briefcase" value={draft.apartment}
          placeholder={language === 'ar' ? 'اختياري' : 'Appartement, étage… (facultatif)'}
          onChangeText={(v) => setDraft(update(draft, 'apartment', v))} />
        <FormField label={copy.postcode} icon="clipboard" value={draft.postcode} error={errors.postcode}
          keyboardType="numeric" placeholder={language === 'ar' ? 'أدخل الرمز البريدي' : 'Entrez votre code postal'}
          onChangeText={(v) => setDraft(update(draft, 'postcode', v))} />
        <FormField label={copy.instructions} icon="edit-2" value={draft.deliveryInstructions}
          placeholder={language === 'ar' ? 'اختياري' : 'Instructions spéciales pour la livraison (facultatif)'}
          onChangeText={(v) => setDraft(update(draft, 'deliveryInstructions', v))} />

        {/* Label Selector */}
        <MayushText variant="inputLabel" color={colors.brand.navy900} style={styles.sectionLabel}>{copy.label}</MayushText>
        <View style={styles.labels}>
          {(['Maison', 'Bureau', 'Autre'] as const).map((label) => {
            const selected = draft.label === label;
            return (
              <TouchableOpacity key={label} accessibilityRole="radio" accessibilityState={{ selected }}
                onPress={() => setDraft(update(draft, 'label', label))}
                style={[styles.labelChoice, selected && styles.labelChoiceSelected]}>
                <MayushIcon name={label === 'Maison' ? 'home' : label === 'Bureau' ? 'briefcase' : 'tag'} size={16}
                  color={selected ? colors.brand.orange500 : colors.brand.navy900} />
                <MayushText variant="caption" color={selected ? colors.brand.orange500 : colors.brand.navy900}>{label}</MayushText>
              </TouchableOpacity>
            );
          })}
        </View>

        {/* Default Toggle */}
        <View style={[styles.defaultRow, isRTL && styles.rowReverse]}>
          <MayushText variant="caption" color={colors.brand.navy900}>{copy.defaultLabel}</MayushText>
          <Switch value={draft.isDefault}
            onValueChange={(v) => setDraft(update(draft, 'isDefault', v))}
            trackColor={{ false: '#E8E8E8', true: '#F8E6D7' }}
            thumbColor={draft.isDefault ? colors.brand.orange500 : colors.surface.white} />
        </View>

        {/* Save Button */}
        <TouchableOpacity accessibilityRole="button" onPress={handleSave} style={styles.saveButton}>
          <MayushIcon name="check" size={18} color={colors.surface.white} />
          <MayushText variant="button" color={colors.surface.white}>{copy.save}</MayushText>
        </TouchableOpacity>
      </ScrollView>
    </KeyboardAvoidingView>
  );
};

const FormField: React.FC<{
  label: string;
  icon: React.ComponentProps<typeof MayushIcon>['name'];
  value: string;
  placeholder: string;
  error?: string;
  keyboardType?: 'default' | 'phone-pad' | 'numeric';
  onChangeText: (value: string) => void;
}> = ({ label, icon, value, error, keyboardType, placeholder, onChangeText }) => (
  <TextField
    containerStyle={styles.field}
    label={label}
    leftIcon={icon}
    value={value}
    error={error}
    placeholder={placeholder}
    keyboardType={keyboardType}
    autoCapitalize="words"
    onChangeText={onChangeText}
  />
);

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white },
  header: {
    height: 56, paddingHorizontal: spacing.md, flexDirection: 'row', alignItems: 'center',
    justifyContent: 'space-between', borderBottomWidth: 1, borderBottomColor: '#E7DED3',
  },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  back: { width: 36, height: 36, justifyContent: 'center' },
  rowReverse: { flexDirection: 'row-reverse' },
  content: { paddingHorizontal: 20, paddingBottom: 30 },
  title: { fontSize: 22, lineHeight: 28, marginTop: 14 },
  subtitle: { marginTop: 2, marginBottom: 10, fontSize: 12 },
  field: { marginTop: 6 },
  sectionLabel: { marginTop: 14, marginBottom: 7 },
  labels: { flexDirection: 'row', gap: 8 },
  labelChoice: {
    flex: 1, height: 36, borderWidth: 1, borderColor: '#E7DED3', borderRadius: 8,
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6,
    backgroundColor: colors.surface.white,
  },
  labelChoiceSelected: { borderColor: colors.brand.orange500, backgroundColor: '#FFF6E8' },
  defaultRow: { height: 44, marginTop: 8, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  saveButton: {
    height: 48, marginTop: 14, borderRadius: 14, flexDirection: 'row', alignItems: 'center',
    justifyContent: 'center', gap: 8, backgroundColor: colors.brand.orange500,
  },
});
