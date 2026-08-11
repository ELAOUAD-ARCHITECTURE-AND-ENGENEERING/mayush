import React, { useState, useEffect } from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
} from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { supportState, ContactDraft } from '../../commerce/supportState';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';

interface ContactSupportFormScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onNavigateAttachFiles: () => void;
  onNavigateSelectOrder: () => void;
  onNavigateReview: () => void;
}

const SUBJECT_OPTIONS = [
  'Retard de livraison de ma commande',
  'Article endommagé à la réception',
  'Annulation de commande demandée',
  'Question sur les matériaux et produits',
  'Problème de paiement ou facturation',
  'Autre demande d\'assistance',
];

export const ContactSupportFormScreen: React.FC<ContactSupportFormScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateAttachFiles,
  onNavigateSelectOrder,
  onNavigateReview,
}) => {
  const [draft, setDraft] = useState<ContactDraft>(supportState.getContactDraft());
  const [language, setLanguage] = useState(accountPreferencesState.getLanguage());
  const [showSubjectPicker, setShowSubjectPicker] = useState(false);
  const [showCategoryPicker, setShowCategoryPicker] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  useEffect(() => {
    const unsubSupport = supportState.subscribe(() => {
      setDraft(supportState.getContactDraft());
    });
    const unsubPref = accountPreferencesState.subscribe(() => {
      setLanguage(accountPreferencesState.getLanguage());
    });
    return () => {
      unsubSupport();
      unsubPref();
    };
  }, []);

  const isRTL = language === 'ar';
  const categories = supportState.getFaqCategories();

  const handleUpdate = (updates: Partial<ContactDraft>) => {
    supportState.setContactDraft(updates);
  };

  const handleNext = () => {
    if (!draft.subject.trim()) {
      setErrorMsg(isRTL ? 'يرجى اختيار أو كتابة موضوع الطلب' : 'Veuillez choisir un sujet de demande.');
      return;
    }
    if (!draft.message.trim()) {
      setErrorMsg(isRTL ? 'يرجى كتابة نص الرسالة' : 'Veuillez décrire votre demande.');
      return;
    }
    setErrorMsg('');
    onNavigateReview();
  };

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onBack} style={styles.backBtn} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="cardTitle" style={styles.headerLogo}>
          MAYUSH<MayushText variant="cardTitle" color={colors.brand.orange500}> DESIGN</MayushText>
        </MayushText>
        <TouchableOpacity onPress={onNavigateReview} style={styles.bellBtn} activeOpacity={0.7}>
          <MayushIcon name="bell" size={20} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Title Section */}
        <View style={styles.titleSection}>
          <View style={styles.headsetCircle}>
            <MayushIcon name="headphones" size={28} color={colors.brand.orange500} />
          </View>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
            {isRTL ? 'التواصل مع الدعم' : 'Contacter le support'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray500} style={[styles.subtitle, isRTL && styles.rtlText]}>
            {isRTL
              ? 'نحن هنا لمساعدتك. أرسل لنا رسالة وسنرد عليك في أقرب وقت.'
              : 'Nous sommes là pour vous aider. Envoyez-nous un message et nous vous répondrons rapidement.'}
          </MayushText>
        </View>

        {errorMsg ? (
          <View style={styles.errorBox}>
            <MayushIcon name="alert-circle" size={16} color="#D9381E" />
            <MayushText variant="smallBody" color="#D9381E" style={{ flex: 1 }}>
              {errorMsg}
            </MayushText>
          </View>
        ) : null}

        {/* Form Card */}
        <View style={styles.formCard}>
          {/* Sujet de votre demande */}
          <MayushText variant="strongBody" color={colors.brand.navy900} style={[styles.label, isRTL && styles.rtlText]}>
            {isRTL ? 'موضوع طلبك *' : 'Sujet de votre demande *'}
          </MayushText>
          <TouchableOpacity
            style={styles.pickerSelector}
            onPress={() => setShowSubjectPicker(!showSubjectPicker)}
            activeOpacity={0.8}
          >
            <MayushText variant="body" color={draft.subject ? colors.brand.navy900 : colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {draft.subject || (isRTL ? 'اختر موضوعاً' : 'Sélectionnez un sujet')}
            </MayushText>
            <MayushIcon name={showSubjectPicker ? 'chevron-up' : 'chevron-down'} size={18} color={colors.neutral.gray500} />
          </TouchableOpacity>

          {showSubjectPicker && (
            <View style={styles.dropdownMenu}>
              {SUBJECT_OPTIONS.map((opt, i) => (
                <TouchableOpacity
                  key={i}
                  style={styles.dropdownOption}
                  onPress={() => {
                    handleUpdate({ subject: opt });
                    setShowSubjectPicker(false);
                  }}
                >
                  <MayushText variant="body" color={colors.brand.navy900}>
                    {opt}
                  </MayushText>
                </TouchableOpacity>
              ))}
            </View>
          )}

          {/* Catégorie */}
          <MayushText variant="strongBody" color={colors.brand.navy900} style={[styles.label, { marginTop: spacing.md }, isRTL && styles.rtlText]}>
            {isRTL ? 'الفئة *' : 'Catégorie *'}
          </MayushText>
          <TouchableOpacity
            style={styles.pickerSelector}
            onPress={() => setShowCategoryPicker(!showCategoryPicker)}
            activeOpacity={0.8}
          >
            <MayushText variant="body" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {categories.find((c) => c.id === draft.categoryId)?.label || 'Commandes & Livraison'}
            </MayushText>
            <MayushIcon name={showCategoryPicker ? 'chevron-up' : 'chevron-down'} size={18} color={colors.neutral.gray500} />
          </TouchableOpacity>

          {showCategoryPicker && (
            <View style={styles.dropdownMenu}>
              {categories.map((cat) => (
                <TouchableOpacity
                  key={cat.id}
                  style={styles.dropdownOption}
                  onPress={() => {
                    handleUpdate({ categoryId: cat.id });
                    setShowCategoryPicker(false);
                  }}
                >
                  <MayushText variant="body" color={colors.brand.navy900}>
                    {cat.label}
                  </MayushText>
                </TouchableOpacity>
              ))}
            </View>
          )}

          {/* Message */}
          <MayushText variant="strongBody" color={colors.brand.navy900} style={[styles.label, { marginTop: spacing.md }, isRTL && styles.rtlText]}>
            {isRTL ? 'رسالتك *' : 'Votre message *'}
          </MayushText>
          <View style={styles.textAreaBox}>
            <TextInput
              style={[styles.textAreaInput, isRTL && styles.rtlText]}
              placeholder={isRTL ? 'صف طلبك بالتفصيل...' : 'Décrivez votre demande en détail...'}
              placeholderTextColor={colors.neutral.gray500}
              multiline
              maxLength={1000}
              value={draft.message}
              onChangeText={(text) => handleUpdate({ message: text })}
            />
            <MayushText variant="smallBody" color={colors.neutral.gray500} style={styles.charCounter}>
              {draft.message.length}/1000
            </MayushText>
          </View>

          {/* Email */}
          <MayushText variant="strongBody" color={colors.brand.navy900} style={[styles.label, { marginTop: spacing.md }, isRTL && styles.rtlText]}>
            {isRTL ? 'بريدك الإلكتروني *' : 'Votre email *'}
          </MayushText>
          <View style={styles.inputBox}>
            <MayushIcon name="mail" size={18} color={colors.neutral.gray500} />
            <TextInput
              style={[styles.textInput, isRTL && styles.rtlText]}
              placeholder={isRTL ? 'أدخل بريدك الإلكتروني' : 'Entrez votre email'}
              placeholderTextColor={colors.neutral.gray500}
              keyboardType="email-address"
              value={draft.email}
              onChangeText={(email) => handleUpdate({ email })}
            />
          </View>

          {/* Preferred Channel */}
          <MayushText variant="strongBody" color={colors.brand.navy900} style={[styles.label, { marginTop: spacing.md }, isRTL && styles.rtlText]}>
            {isRTL ? 'قناة الرد المفضلة *' : 'Canal de réponse préféré *'}
          </MayushText>
          <View style={styles.channelRow}>
            {(['Email', 'WhatsApp'] as const).map((ch) => {
              const active = draft.preferredChannel === ch;
              return (
                <TouchableOpacity
                  key={ch}
                  style={[styles.channelChip, active && styles.channelChipActive]}
                  onPress={() => handleUpdate({ preferredChannel: ch })}
                  activeOpacity={0.7}
                >
                  <MayushIcon
                    name={ch === 'Email' ? 'mail' : 'message-square'}
                    size={16}
                    color={active ? colors.brand.orange500 : colors.brand.navy900}
                  />
                  <MayushText variant="strongBody" color={active ? colors.brand.orange500 : colors.brand.navy900}>
                    {ch}
                  </MayushText>
                </TouchableOpacity>
              );
            })}
          </View>

          {/* Order Association Action */}
          <TouchableOpacity style={styles.subActionRow} onPress={onNavigateSelectOrder} activeOpacity={0.7}>
            <View style={styles.subActionLeft}>
              <MayushIcon name="shopping-bag" size={18} color={colors.brand.navy900} />
              <View>
                <MayushText variant="strongBody" color={colors.brand.navy900}>
                  {draft.selectedOrderId
                    ? `${isRTL ? 'الطلب المحدد: ' : 'Commande : '}${draft.selectedOrderId}`
                    : isRTL ? 'ربط بطلب (اختياري)' : 'Associer à une commande (optionnel)'}
                </MayushText>
                <MayushText variant="smallBody" color={colors.neutral.gray500}>
                  {draft.selectedOrderId ? (isRTL ? 'انقر للتغيير' : 'Cliquer pour modifier') : (isRTL ? 'اختر الطلب المعني' : 'Sélectionner la commande concernée')}
                </MayushText>
              </View>
            </View>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
          </TouchableOpacity>

          {/* Attachments Action */}
          <TouchableOpacity style={styles.subActionRow} onPress={onNavigateAttachFiles} activeOpacity={0.7}>
            <View style={styles.subActionLeft}>
              <MayushIcon name="paperclip" size={18} color={colors.brand.navy900} />
              <View>
                <MayushText variant="strongBody" color={colors.brand.navy900}>
                  {draft.attachments.length > 0
                    ? `${draft.attachments.length} ${isRTL ? 'ملفات مرفقة' : 'fichier(s) joint(s)'}`
                    : isRTL ? 'إرفاق ملف (اختياري)' : 'Joindre un fichier (optionnel)'}
                </MayushText>
                <MayushText variant="smallBody" color={colors.neutral.gray500}>
                  {isRTL ? 'النماذج المقبولة: JPG, PNG, PDF (حد أقصى 5 ميغا)' : 'Formats acceptés : JPG, PNG, PDF (max. 5 Mo)'}
                </MayushText>
              </View>
            </View>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
          </TouchableOpacity>
        </View>

        {/* Security Info Disclaimer */}
        <View style={styles.securityRow}>
          <MayushIcon name="lock" size={16} color={colors.brand.orange500} />
          <MayushText variant="smallBody" color={colors.neutral.gray500} style={{ flex: 1 }}>
            {isRTL
              ? 'معلوماتك آمنة ولن يتم مشاركتها أبداً.'
              : 'Vos informations sont sécurisées et ne seront jamais partagées.'}
          </MayushText>
        </View>

        {/* Primary CTA */}
        <TouchableOpacity style={styles.primaryBtn} onPress={handleNext} activeOpacity={0.85}>
          <MayushText variant="strongBody" color={colors.neutral.white}>
            {isRTL ? 'إرسال طلبي' : 'Envoyer ma demande'}
          </MayushText>
        </TouchableOpacity>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FAF8F5' },
  header: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: spacing.md, paddingTop: 48, paddingBottom: spacing.sm,
    backgroundColor: '#FAF8F5',
  },
  backBtn: { padding: spacing.xs },
  headerLogo: { fontSize: 18, fontWeight: '800', color: colors.brand.navy900 },
  bellBtn: { padding: spacing.xs },
  scrollView: { flex: 1 },
  scrollContent: { padding: spacing.md, paddingBottom: 40 },
  titleSection: { alignItems: 'center', marginBottom: spacing.md },
  headsetCircle: {
    width: 56, height: 56, borderRadius: 28, backgroundColor: 'rgba(232,125,62,0.1)',
    alignItems: 'center', justifyContent: 'center', marginBottom: spacing.xs,
  },
  title: { textAlign: 'center', marginBottom: 4 },
  subtitle: { textAlign: 'center', fontSize: 13, lineHeight: 18 },
  errorBox: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.xs,
    backgroundColor: '#FDE8E8', borderWidth: 1, borderColor: '#F8B4B4',
    borderRadius: 12, padding: spacing.sm, marginBottom: spacing.md,
  },
  formCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)', marginBottom: spacing.md,
  },
  label: { marginBottom: 6, fontSize: 13 },
  pickerSelector: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    backgroundColor: '#FAF8F5', borderWidth: 1, borderColor: colors.neutral.gray300,
    borderRadius: 12, paddingHorizontal: spacing.sm, paddingVertical: 12,
  },
  dropdownMenu: {
    backgroundColor: colors.neutral.white, borderWidth: 1, borderColor: colors.neutral.gray300,
    borderRadius: 12, marginTop: 4, overflow: 'hidden',
  },
  dropdownOption: { paddingHorizontal: spacing.sm, paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: colors.neutral.gray100 },
  textAreaBox: {
    backgroundColor: '#FAF8F5', borderWidth: 1, borderColor: colors.neutral.gray300,
    borderRadius: 12, paddingHorizontal: spacing.sm, paddingTop: 10, paddingBottom: 6,
  },
  textAreaInput: { minHeight: 90, textAlignVertical: 'top', fontSize: 13, color: colors.brand.navy900 },
  charCounter: { textAlign: 'right', fontSize: 11, marginTop: 4 },
  inputBox: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.xs,
    backgroundColor: '#FAF8F5', borderWidth: 1, borderColor: colors.neutral.gray300,
    borderRadius: 12, paddingHorizontal: spacing.sm, paddingVertical: 10,
  },
  textInput: { flex: 1, fontSize: 13, color: colors.brand.navy900 },
  channelRow: { flexDirection: 'row', gap: spacing.sm, marginTop: 4 },
  channelChip: {
    flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6,
    backgroundColor: '#FAF8F5', borderWidth: 1, borderColor: colors.neutral.gray300,
    borderRadius: 12, paddingVertical: 12,
  },
  channelChipActive: { backgroundColor: '#FFF2EB', borderColor: colors.brand.orange500 },
  subActionRow: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    backgroundColor: '#FAF8F5', borderWidth: 1, borderColor: colors.neutral.gray300,
    borderRadius: 12, paddingHorizontal: spacing.sm, paddingVertical: 12, marginTop: spacing.md,
  },
  subActionLeft: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs, flex: 1 },
  securityRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs, marginBottom: spacing.md, paddingHorizontal: spacing.xs },
  primaryBtn: {
    backgroundColor: colors.brand.orange500, borderRadius: 14, paddingVertical: 14,
    alignItems: 'center', justifyContent: 'center',
  },
  rtlText: { textAlign: 'right' },
});
