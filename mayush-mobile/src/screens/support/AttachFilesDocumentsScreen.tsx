import React, { useState, useEffect } from 'react';
import { View, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { supportState, SupportAttachment } from '../../commerce/supportState';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';

interface AttachFilesDocumentsScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onContinue: () => void;
}

const SAMPLE_FILES: SupportAttachment[] = [
  { id: 'sample-1', name: 'Canapé Dune 3 places.jpg', size: '2,4 Mo', type: 'image' },
  { id: 'sample-2', name: 'Facture_MAY-2026-001842.pdf', size: '1,1 Mo', type: 'document' },
  { id: 'sample-3', name: 'Table Noya – détail.jpg', size: '1,8 Mo', type: 'image' },
];

export const AttachFilesDocumentsScreen: React.FC<AttachFilesDocumentsScreenProps> = ({
  onNavigateTab,
  onBack,
  onContinue,
}) => {
  const [draft, setDraft] = useState(supportState.getContactDraft());
  const [language, setLanguage] = useState(accountPreferencesState.getLanguage());

  useEffect(() => {
    const unsubSupport = supportState.subscribe(() => {
      setDraft(supportState.getContactDraft());
    });
    const unsubPref = accountPreferencesState.subscribe(() => {
      setLanguage(accountPreferencesState.getLanguage());
    });

    // Seed default sample files if empty for prototype fidelity
    if (supportState.getContactDraft().attachments.length === 0) {
      supportState.setContactDraft({ attachments: [...SAMPLE_FILES] });
    }

    return () => {
      unsubSupport();
      unsubPref();
    };
  }, []);

  const isRTL = language === 'ar';

  const handleAddSample = (type: 'image' | 'document') => {
    if (draft.attachments.length >= 5) return;
    const newFile: SupportAttachment = {
      id: `att-${Date.now()}`,
      name: type === 'image' ? `Photo_justificative_${draft.attachments.length + 1}.jpg` : `Document_piece_${draft.attachments.length + 1}.pdf`,
      size: '1,5 Mo',
      type,
    };
    supportState.setContactDraft({ attachments: [...draft.attachments, newFile] });
  };

  const handleRemove = (fileId: string) => {
    const updated = draft.attachments.filter((f) => f.id !== fileId);
    supportState.setContactDraft({ attachments: updated });
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
        <TouchableOpacity onPress={onContinue} style={styles.bellBtn} activeOpacity={0.7}>
          <MayushIcon name="bell" size={20} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Title Section */}
        <View style={styles.titleSection}>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
            {isRTL ? 'إرفاق المستندات' : 'Joindre des pièces'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray500} style={[styles.subtitle, isRTL && styles.rtlText]}>
            {isRTL
              ? 'أضف صوراً أو مستندات لمساعدتنا في معالجة طلبك بشكل أسرع.'
              : 'Ajoutez des images ou des documents pour nous aider à traiter votre demande plus rapidement.'}
          </MayushText>
        </View>

        {/* 3 Upload Action Cards */}
        <View style={styles.optionsRow}>
          <TouchableOpacity style={styles.optionCard} onPress={() => handleAddSample('image')} activeOpacity={0.8}>
            <View style={styles.optionIconCircle}>
              <MayushIcon name="image" size={24} color={colors.brand.orange500} />
            </View>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.optionTitle}>
              {isRTL ? 'إضافة صورة' : 'Ajouter une image'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              JPG, PNG
            </MayushText>
          </TouchableOpacity>

          <TouchableOpacity style={styles.optionCard} onPress={() => handleAddSample('document')} activeOpacity={0.8}>
            <View style={styles.optionIconCircle}>
              <MayushIcon name="file-text" size={24} color={colors.brand.orange500} />
            </View>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.optionTitle}>
              {isRTL ? 'إضافة مستند' : 'Ajouter un document'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              PDF, DOC, DOCX
            </MayushText>
          </TouchableOpacity>

          <TouchableOpacity style={styles.optionCard} onPress={() => handleAddSample('image')} activeOpacity={0.8}>
            <View style={styles.optionIconCircle}>
              <MayushIcon name="camera" size={24} color={colors.brand.orange500} />
            </View>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.optionTitle}>
              {isRTL ? 'التقاط صورة' : 'Prendre une photo'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              {isRTL ? 'الكاميرا' : 'Caméra'}
            </MayushText>
          </TouchableOpacity>
        </View>

        {/* File Limits Info Notice */}
        <View style={styles.infoCard}>
          <MayushIcon name="info" size={18} color={colors.brand.orange500} />
          <View style={{ flex: 1 }}>
            <MayushText variant="smallBody" color={colors.brand.navy900} style={{ fontWeight: '600' }}>
              {isRTL ? 'الحجم الأقصى لكل ملف: 10 ميغا' : 'Taille maximale par fichier : 10 Mo'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              {isRTL ? 'النماذج المقبولة: JPG, PNG, PDF, DOC, DOCX' : 'Formats acceptés : JPG, PNG, PDF, DOC, DOCX'}
            </MayushText>
          </View>
        </View>

        {/* Attached Files List */}
        <MayushText variant="strongBody" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>
          {isRTL ? `المرفقات (${draft.attachments.length}/5)` : `Pièces jointes (${draft.attachments.length}/5)`}
        </MayushText>

        <View style={styles.filesCard}>
          {draft.attachments.length === 0 ? (
            <View style={styles.emptyFilesBox}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'لم يتم إرفاق أي ملف بعد.' : 'Aucune pièce jointe pour le moment.'}
              </MayushText>
            </View>
          ) : (
            draft.attachments.map((file, idx) => (
              <View key={file.id}>
                <View style={styles.fileRow}>
                  <View style={styles.fileIconBadge}>
                    <MayushIcon name={file.type === 'image' ? 'image' : 'file-text'} size={18} color={colors.brand.orange500} />
                  </View>
                  <View style={{ flex: 1 }}>
                    <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 13 }} numberOfLines={1}>
                      {file.name}
                    </MayushText>
                    <MayushText variant="smallBody" color={colors.neutral.gray500}>
                      {file.size}
                    </MayushText>
                  </View>
                  <TouchableOpacity style={styles.removeBtn} onPress={() => handleRemove(file.id)} activeOpacity={0.7}>
                    <View style={styles.removeCircle}>
                      <MayushIcon name="x" size={12} color="#D9381E" />
                    </View>
                    <MayushText variant="smallBody" color="#D9381E" style={{ fontSize: 11, fontWeight: '600' }}>
                      {isRTL ? 'حذف' : 'Supprimer'}
                    </MayushText>
                  </TouchableOpacity>
                </View>
                {idx < draft.attachments.length - 1 && <View style={styles.divider} />}
              </View>
            ))
          )}
        </View>

        {/* Add Another File Button */}
        {draft.attachments.length < 5 && (
          <TouchableOpacity style={styles.addAnotherBtn} onPress={() => handleAddSample('image')} activeOpacity={0.8}>
            <MayushIcon name="plus-circle" size={18} color={colors.brand.navy900} />
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {isRTL ? 'إضافة مرفق آخر' : 'Ajouter une autre pièce'}
            </MayushText>
          </TouchableOpacity>
        )}

        {/* Primary Continue Button */}
        <TouchableOpacity style={styles.primaryBtn} onPress={onContinue} activeOpacity={0.85}>
          <MayushText variant="strongBody" color={colors.neutral.white}>
            {isRTL ? 'متابعة' : 'Continuer'}
          </MayushText>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.white} />
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
  title: { textAlign: 'center', marginBottom: 4 },
  subtitle: { textAlign: 'center', fontSize: 13, lineHeight: 18 },
  optionsRow: { flexDirection: 'row', gap: spacing.xs, marginBottom: spacing.md },
  optionCard: {
    flex: 1, backgroundColor: colors.neutral.white, borderRadius: 16,
    padding: 12, alignItems: 'center', borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)',
  },
  optionIconCircle: {
    width: 44, height: 44, borderRadius: 22, backgroundColor: '#FFF2EB',
    alignItems: 'center', justifyContent: 'center', marginBottom: 8,
  },
  optionTitle: { fontSize: 11, textAlign: 'center', marginBottom: 2 },
  infoCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.xs,
    backgroundColor: '#FFF2EB', borderRadius: 14, padding: spacing.sm,
    borderWidth: 1, borderColor: 'rgba(232,125,62,0.2)', marginBottom: spacing.md,
  },
  sectionTitle: { marginBottom: spacing.xs, fontSize: 14 },
  filesCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, paddingHorizontal: spacing.md,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)', marginBottom: spacing.md,
  },
  emptyFilesBox: { paddingVertical: spacing.md, alignItems: 'center' },
  fileRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs, paddingVertical: 12 },
  fileIconBadge: {
    width: 36, height: 36, borderRadius: 18, backgroundColor: '#FFF2EB',
    alignItems: 'center', justifyContent: 'center',
  },
  removeBtn: { alignItems: 'center', gap: 2, paddingLeft: spacing.xs },
  removeCircle: { width: 18, height: 18, borderRadius: 9, backgroundColor: '#FDE8E8', alignItems: 'center', justifyContent: 'center' },
  divider: { height: 1, backgroundColor: colors.neutral.gray300 },
  addAnotherBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: '#FAF8F5', borderWidth: 1, borderStyle: 'dashed', borderColor: colors.neutral.gray300,
    borderRadius: 14, paddingVertical: 12, marginBottom: spacing.lg,
  },
  primaryBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.brand.orange500, borderRadius: 14, paddingVertical: 14,
  },
  rtlText: { textAlign: 'right' },
});
