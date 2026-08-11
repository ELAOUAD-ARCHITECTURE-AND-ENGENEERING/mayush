import React from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  Switch,
  TouchableOpacity,
  View,
} from 'react-native';
import { AddressDraft, AddressDraftErrors } from '../../commerce/checkoutState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { TextField } from '../../design-system/components/forms/TextField';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

export interface AddAddressFormScreenProps {
  draft: AddressDraft;
  errors: AddressDraftErrors;
  onChange: (next: AddressDraft) => void;
  onBack: () => void;
  onSave: () => void;
  onChooseCity?: () => void;
  onChooseZone?: () => void;
}

const update = (draft: AddressDraft, key: keyof AddressDraft, value: string | boolean): AddressDraft => ({ ...draft, [key]: value });

export const AddAddressFormScreen: React.FC<AddAddressFormScreenProps> = ({ draft, errors, onChange, onBack, onSave, onChooseCity, onChooseZone }) => {
  const { isRTL, language } = useTheme();
  const copy = language === 'ar'
    ? {
      title: 'أضف عنوانًا جديدًا',
      subtitle: 'أضف معلوماتك لتوصيل سريع وموثوق.',
      name: 'الاسم الكامل',
      phone: 'رقم الهاتف',
      city: 'المدينة',
      zone: 'منطقة التوصيل',
      address: 'العنوان',
      apartment: 'الشقة، الطابق...',
      postcode: 'الرمز البريدي',
      instructions: 'تعليمات التوصيل',
      label: 'تسمية العنوان',
      default: 'اجعل هذا العنوان افتراضيًا',
      save: 'حفظ العنوان',
    }
    : {
      title: 'Ajouter une adresse',
      subtitle: 'Ajoutez vos informations pour une livraison rapide et fiable.',
      name: 'Nom complet',
      phone: 'Numéro de téléphone',
      city: 'Ville',
      zone: 'Zone de livraison',
      address: 'Adresse',
      apartment: 'Appartement, étage...',
      postcode: 'Code postal',
      instructions: 'Instructions de livraison',
      label: 'Étiquette de l’adresse',
      default: 'Définir comme adresse par défaut',
      save: 'Enregistrer l’adresse',
    };
  const textAlign = isRTL ? 'right' : 'left';

  return (
    <KeyboardAvoidingView style={styles.screen} behavior={Platform.select({ ios: 'padding', default: undefined })}>
      <View style={styles.header}>
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={language === 'ar' ? 'رجوع' : 'Retour'} onPress={onBack} style={styles.back}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushLogo width={132} height={39} />
        <View style={styles.headerSpacer} />
      </View>
      <ScrollView keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, { textAlign }]}>{copy.title}</MayushText>
        <MayushText variant="caption" color={colors.neutral.gray700} style={[styles.subtitle, { textAlign }]}>{copy.subtitle}</MayushText>
        <FormField label={copy.name} icon="user" value={draft.name} error={errors.name} placeholder={language === 'ar' ? 'أدخل اسمك الكامل' : 'Entrez votre nom complet'} onChangeText={(value) => onChange(update(draft, 'name', value))} />
        <FormField label={copy.phone} icon="phone" value={draft.phone} error={errors.phone} keyboardType="phone-pad" placeholder="+212 6 XX XX XX XX" onChangeText={(value) => onChange(update(draft, 'phone', value))} />
        <FormSelector label={copy.city} icon="map-pin" value={draft.city} error={errors.city} placeholder={language === 'ar' ? 'اختر مدينتك' : 'Sélectionnez votre ville'} onPress={onChooseCity || (() => undefined)} />
        <FormSelector label={copy.zone} icon="map" value={draft.zone} error={errors.zone} placeholder={language === 'ar' ? 'اختر منطقة التوصيل' : 'Sélectionnez votre zone de livraison'} onPress={onChooseZone || (() => undefined)} />
        <FormField label={copy.address} icon="home" value={draft.addressLine} error={errors.addressLine} placeholder={language === 'ar' ? 'رقم، شارع، حي...' : 'Numéro, rue, quartier…'} onChangeText={(value) => onChange(update(draft, 'addressLine', value))} />
        <FormField label={copy.apartment} icon="briefcase" value={draft.apartment} placeholder={language === 'ar' ? 'اختياري' : 'Appartement, étage, bâtiment… (facultatif)'} onChangeText={(value) => onChange(update(draft, 'apartment', value))} />
        <FormField label={copy.postcode} icon="clipboard" value={draft.postcode} error={errors.postcode} keyboardType="numeric" placeholder={language === 'ar' ? 'أدخل الرمز البريدي' : 'Entrez votre code postal'} onChangeText={(value) => onChange(update(draft, 'postcode', value))} />
        <FormField label={copy.instructions} icon="edit-2" value={draft.deliveryInstructions} placeholder={language === 'ar' ? 'اختياري' : 'Instructions spéciales pour la livraison (facultatif)'} onChangeText={(value) => onChange(update(draft, 'deliveryInstructions', value))} />
        <MayushText variant="inputLabel" color={colors.brand.navy900} style={styles.sectionLabel}>{copy.label}</MayushText>
        <View style={styles.labels}>
          {(['Maison', 'Bureau', 'Autre'] as const).map((label) => {
            const selected = draft.label === label;
            return <TouchableOpacity key={label} accessibilityRole="radio" accessibilityState={{ selected }} onPress={() => onChange(update(draft, 'label', label))} style={[styles.labelChoice, selected && styles.labelChoiceSelected]}>
              <MayushIcon name={label === 'Maison' ? 'home' : label === 'Bureau' ? 'briefcase' : 'tag'} size={16} color={selected ? colors.brand.orange500 : colors.brand.navy900} />
              <MayushText variant="caption" color={selected ? colors.brand.orange500 : colors.brand.navy900}>{label}</MayushText>
            </TouchableOpacity>;
          })}
        </View>
        <View style={styles.defaultRow}>
          <MayushText variant="caption" color={colors.brand.navy900}>{copy.default}</MayushText>
          <Switch value={draft.isDefault} onValueChange={(value) => onChange(update(draft, 'isDefault', value))} trackColor={{ false: '#E8E8E8', true: '#F8E6D7' }} thumbColor={draft.isDefault ? colors.brand.orange500 : colors.surface.white} />
        </View>
        <TouchableOpacity accessibilityRole="button" onPress={onSave} style={styles.saveButton}>
          <MayushIcon name="clipboard" size={18} color={colors.surface.white} />
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

const FormSelector: React.FC<{ label: string; icon: React.ComponentProps<typeof MayushIcon>['name']; value: string; placeholder: string; error?: string; onPress: () => void }> = ({ label, icon, value, placeholder, error, onPress }) => (
  <View style={styles.field}>
    <MayushText variant="inputLabel" color={colors.brand.navy900}>{label}</MayushText>
    <TouchableOpacity accessibilityRole="button" onPress={onPress} style={[styles.selector, error && styles.selectorError]}>
      <MayushIcon name={icon} size={18} color={colors.brand.navy900} />
      <MayushText variant="body" color={value ? colors.brand.navy900 : colors.neutral.gray500} style={styles.selectorText}>{value || placeholder}</MayushText>
      <MayushIcon name="chevron-right" size={18} color={colors.neutral.gray500} />
    </TouchableOpacity>
    {error ? <MayushText variant="caption" color="#B42318">{error}</MayushText> : null}
  </View>
);

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white },
  header: { height: 64, paddingHorizontal: 20, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: colors.surface.white, borderBottomWidth: 1, borderBottomColor: colors.surface.borderWarm },
  back: { width: 36, height: 36, justifyContent: 'center' },
  headerSpacer: { width: 36 },
  content: { paddingHorizontal: 20, paddingBottom: 24 },
  title: { fontSize: 24, lineHeight: 30, marginTop: 12 },
  subtitle: { marginTop: 2, marginBottom: 12 },
  field: { marginTop: 6 },
  selector: { height: 48, marginTop: 6, paddingHorizontal: 12, borderWidth: 1, borderColor: colors.surface.borderWarm, borderRadius: 10, flexDirection: 'row', alignItems: 'center', gap: 9, backgroundColor: colors.surface.white },
  selectorError: { borderColor: '#B42318' },
  selectorText: { flex: 1 },
  sectionLabel: { marginTop: 12, marginBottom: 7 },
  labels: { flexDirection: 'row', gap: 8 },
  labelChoice: { flex: 1, height: 36, borderWidth: 1, borderColor: colors.surface.borderWarm, borderRadius: 8, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, backgroundColor: colors.surface.white },
  labelChoiceSelected: { borderColor: colors.brand.orange500, backgroundColor: colors.brand.orange100 },
  defaultRow: { height: 44, marginTop: 8, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  saveButton: { height: 44, marginTop: 12, borderRadius: 12, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, backgroundColor: colors.brand.orange500 },
});
