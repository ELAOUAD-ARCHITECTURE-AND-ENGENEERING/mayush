import React, { useState } from 'react';
import { Image, ScrollView, StyleSheet, TextInput, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { supportState } from '../../commerce/supportState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface HelpCenterHomeScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateCategory?: (categoryId: string) => void;
  onNavigateSearch?: (query: string) => void;
  onNavigateFaq?: () => void;
  onNavigateRequests?: () => void;
  onNavigateContactSupport?: () => void;
}

export const HelpCenterHomeScreen: React.FC<HelpCenterHomeScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateCategory,
  onNavigateSearch,
  onNavigateFaq,
  onNavigateRequests,
  onNavigateContactSupport,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [searchQuery, setSearchQueryText] = useState(supportState.getSearchQuery());

  const recentRequests = supportState.getSupportRequests().slice(0, 2);

  const categories = [
    { id: 'commandes', labelFr: 'Commandes', labelAr: 'الطلبات', icon: 'file-text', color: '#E87D3E' },
    { id: 'paiement', labelFr: 'Paiement', labelAr: 'الدفع', icon: 'shield-check', color: '#1B9A59' },
    { id: 'livraison', labelFr: 'Livraison', labelAr: 'التوصيل', icon: 'truck', color: '#E87D3E' },
    { id: 'retours', labelFr: 'Retours et remboursements', labelAr: 'الإرجاع والاسترداد', icon: 'rotate-ccw', color: '#E87D3E' },
    { id: 'compte', labelFr: 'Compte et sécurité', labelAr: 'الحساب والأمان', icon: 'lock', color: '#2B3A4A' },
    { id: 'produits', labelFr: 'Produits', labelAr: 'المنتجات', icon: 'grid', color: '#2B3A4A' },
  ];

  const handleSearchSubmit = () => {
    supportState.setSearchQuery(searchQuery);
    onNavigateSearch?.(searchQuery);
  };

  return (
    <View style={styles.container}>
      {/* Top Header */}
      <View style={[styles.topHeader, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.iconBtn} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <Image
          source={require('../../../assets/brand/logo-transparent.png')}
          style={styles.logoImage}
          resizeMode="contain"
        />
        <TouchableOpacity style={styles.iconBtn} activeOpacity={0.7}>
          <MayushIcon name="bell" size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Title Block */}
        <View style={styles.titleBlock}>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.mainTitle, isRTL && styles.rtlText]}>
            {isRTL ? 'مركز المساعدة' : 'Centre d\'aide'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray500} style={[styles.mainSubtitle, isRTL && styles.rtlText]}>
            {isRTL ? 'نحن هنا لمرافقتك ومساعدتك' : 'Nous sommes là pour vous accompagner'}
          </MayushText>
        </View>

        {/* Search Bar */}
        <View style={[styles.searchBox, isRTL && styles.rtlRow]}>
          <MayushIcon name="search" size={20} color={colors.neutral.gray500} />
          <TextInput
            style={[styles.searchInput, isRTL && styles.rtlText]}
            placeholder={isRTL ? 'البحث عن مساعدة، مقال أو سؤال...' : 'Rechercher une aide, un article ou une question'}
            placeholderTextColor={colors.neutral.gray500}
            value={searchQuery}
            onChangeText={setSearchQueryText}
            onSubmitEditing={handleSearchSubmit}
            returnKeyType="search"
          />
          {searchQuery.length > 0 && (
            <TouchableOpacity onPress={handleSearchSubmit} activeOpacity={0.7}>
              <MayushIcon name="arrow-right" size={18} color={colors.brand.orange500} />
            </TouchableOpacity>
          )}
        </View>

        {/* Category Grid Section */}
        <View style={styles.section}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>
            {isRTL ? 'هل تحتاج إلى مساعدة بشأن؟' : 'Besoin d\'aide sur ?'}
          </MayushText>

          <View style={styles.categoryGrid}>
            {categories.map((cat) => (
              <TouchableOpacity
                key={cat.id}
                style={styles.gridCard}
                onPress={() => {
                  supportState.setSelectedHelpCategory(cat.id);
                  onNavigateCategory?.(cat.id);
                }}
                activeOpacity={0.7}
              >
                <View style={styles.gridIconCircle}>
                  <MayushIcon name={cat.icon as any} size={22} color={cat.color} />
                </View>
                <MayushText variant="caption" color={colors.brand.navy900} style={styles.gridLabel}>
                  {isRTL ? cat.labelAr : cat.labelFr}
                </MayushText>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        {/* Recent Requests Section */}
        <View style={styles.section}>
          <View style={[styles.sectionHeaderRow, isRTL && styles.rtlRow]}>
            <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {isRTL ? 'الطلبات الأخيرة' : 'Demandes récentes'}
            </MayushText>
            <TouchableOpacity onPress={onNavigateRequests} activeOpacity={0.7}>
              <MayushText variant="strongBody" color={colors.brand.orange500}>
                {isRTL ? 'عرض الكل' : 'Voir tout'}
              </MayushText>
            </TouchableOpacity>
          </View>

          {recentRequests.map((req) => {
            const isCompleted = req.status === 'resolved' || req.status === 'closed';
            return (
              <TouchableOpacity
                key={req.id}
                style={[styles.requestCard, isRTL && styles.rtlRow]}
                onPress={onNavigateRequests}
                activeOpacity={0.7}
              >
                <View style={[styles.reqIconBox, { backgroundColor: isCompleted ? 'rgba(27,154,89,0.1)' : 'rgba(232,125,62,0.1)' }]}>
                  <MayushIcon
                    name={isCompleted ? 'check-circle' : 'file-text'}
                    size={20}
                    color={isCompleted ? '#1B9A59' : '#E87D3E'}
                  />
                </View>
                <View style={styles.reqTextCol}>
                  <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                    {req.reference}
                  </MayushText>
                  <MayushText variant="smallBody" color={isCompleted ? '#1B9A59' : '#E87D3E'} style={isRTL && styles.rtlText}>
                    {isRTL ? req.titleAr : req.title}
                  </MayushText>
                  <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                    {req.date}
                  </MayushText>
                </View>
                <View style={[styles.statusBadge, { backgroundColor: isCompleted ? 'rgba(27,154,89,0.12)' : 'rgba(232,125,62,0.12)' }]}>
                  <MayushIcon name={isCompleted ? 'check-circle' : 'clock'} size={14} color={isCompleted ? '#1B9A59' : '#E87D3E'} />
                  <MayushText variant="caption" color={isCompleted ? '#1B9A59' : '#E87D3E'} style={{ fontWeight: '600' }}>
                    {isRTL ? req.statusLabelAr : req.statusLabel}
                  </MayushText>
                </View>
                <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
              </TouchableOpacity>
            );
          })}
        </View>

        {/* FAQ Shortcut Card */}
        <TouchableOpacity style={[styles.faqCard, isRTL && styles.rtlRow]} onPress={onNavigateFaq} activeOpacity={0.7}>
          <View style={styles.faqIconBox}>
            <MayushIcon name="help-circle" size={22} color={colors.brand.navy900} />
          </View>
          <View style={styles.reqTextCol}>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {isRTL ? 'الأسئلة الشائعة (FAQ)' : 'Questions fréquentes (FAQ)'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {isRTL ? 'اعثر بسرعة على إجابات لأسئلتك.' : 'Trouvez rapidement des réponses à vos questions.'}
            </MayushText>
          </View>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
        </TouchableOpacity>

        {/* Contact Support CTA Button */}
        <TouchableOpacity style={styles.contactBtn} onPress={onNavigateContactSupport} activeOpacity={0.85}>
          <MayushIcon name="headphones" size={20} color={colors.neutral.white} />
          <MayushText variant="strongBody" color={colors.neutral.white}>
            {isRTL ? 'التواصل مع الدعم' : 'Contacter le support'}
          </MayushText>
        </TouchableOpacity>

        {/* Operating Hours Banner */}
        <View style={[styles.hoursBanner, isRTL && styles.rtlRow]}>
          <MayushIcon name="clock" size={20} color={colors.neutral.gray500} />
          <View style={{ flex: 1 }}>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {isRTL ? 'نحن متاحون' : 'Nous sommes disponibles'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {isRTL ? 'من الاثنين إلى الجمعة من 9 صباحاً إلى 6 مساءً.' : 'Du lundi au vendredi de 9h à 18h.'}
            </MayushText>
          </View>
        </View>
      </ScrollView>

      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FAF8F5' },
  topHeader: {
    height: 56, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: spacing.md, backgroundColor: colors.neutral.white,
    borderBottomWidth: 1, borderBottomColor: colors.neutral.gray300,
  },
  iconBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  logoImage: { height: 28, width: 120 },
  scrollContent: { padding: spacing.md, gap: spacing.md, paddingBottom: 100 },
  titleBlock: { alignItems: 'center', gap: 4, marginVertical: 4 },
  mainTitle: { fontSize: 24, fontWeight: '700', fontFamily: 'serif', color: colors.brand.navy900 },
  mainSubtitle: { fontSize: 14, textAlign: 'center' },
  searchBox: {
    flexDirection: 'row', alignItems: 'center', backgroundColor: colors.neutral.white,
    borderRadius: 14, borderWidth: 1, borderColor: colors.neutral.gray300,
    paddingHorizontal: spacing.sm, height: 48, gap: spacing.xs,
  },
  searchInput: { flex: 1, fontSize: 14, color: colors.brand.navy900, paddingVertical: 0 },
  section: { gap: spacing.sm },
  sectionTitle: { fontSize: 16, fontWeight: '700' },
  sectionHeaderRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  categoryGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10, justifyContent: 'space-between' },
  gridCard: {
    width: '31%', backgroundColor: colors.neutral.white, borderRadius: 14,
    paddingVertical: 16, paddingHorizontal: 6, alignItems: 'center', gap: 8,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)',
  },
  gridIconCircle: { width: 44, height: 44, borderRadius: 22, backgroundColor: 'rgba(0,0,0,0.03)', alignItems: 'center', justifyContent: 'center' },
  gridLabel: { textAlign: 'center', fontSize: 12, fontWeight: '600' },
  requestCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.sm,
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)', marginVertical: 2,
  },
  reqIconBox: { width: 40, height: 40, borderRadius: 10, alignItems: 'center', justifyContent: 'center' },
  reqTextCol: { flex: 1, gap: 2 },
  statusBadge: { flexDirection: 'row', alignItems: 'center', gap: 4, paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
  faqCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.sm,
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)',
  },
  faqIconBox: { width: 44, height: 44, borderRadius: 22, backgroundColor: 'rgba(0,0,0,0.03)', alignItems: 'center', justifyContent: 'center' },
  contactBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.brand.orange500, borderRadius: 14, height: 50,
  },
  hoursBanner: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.sm,
    backgroundColor: 'rgba(0,0,0,0.03)', borderRadius: 14, padding: spacing.md,
  },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
