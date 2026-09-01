import React from 'react';
import { Linking, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { supportState } from '../../commerce/supportState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface HelpSupportHubScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateFaq?: () => void;
  onNavigateHelpCenter?: () => void;
  onNavigateRecentRequests?: () => void;
}

export const HelpSupportHubScreen: React.FC<HelpSupportHubScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateFaq,
  onNavigateHelpCenter,
  onNavigateRecentRequests,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const channels = supportState.getContactChannels();
  const categories = supportState.getFaqCategories();

  const handleChannelPress = (channel: { type: string; value: string }) => {
    if (channel.type === 'phone') {
      Linking.openURL(`tel:${channel.value.replace(/\s/g, '')}`).catch(() => {});
    } else if (channel.type === 'email') {
      Linking.openURL(`mailto:${channel.value}`).catch(() => {});
    }
    // Chat: frontend-only presentation, no real chat backend
  };

  const navCards = [
    {
      id: 'faq', icon: 'help-circle', color: colors.brand.orange500,
      label: isRTL ? 'الأسئلة الشائعة' : 'Questions Fréquentes',
      sub: isRTL ? '7 أسئلة في 5 فئات' : '7 questions dans 5 catégories',
      onPress: onNavigateFaq,
    },
    {
      id: 'help', icon: 'life-buoy', color: colors.brand.navy900,
      label: isRTL ? 'مركز المساعدة' : 'Centre d\'Aide',
      sub: isRTL ? 'مواضيع ومصادر الدعم' : 'Sujets et ressources d\'aide',
      onPress: onNavigateHelpCenter,
    },
    {
      id: 'requests', icon: 'file-text', color: colors.semantic.warning,
      label: isRTL ? 'طلباتي' : 'Mes Demandes',
      sub: isRTL ? 'الطلبات الأخيرة والتتبع' : 'Demandes récentes et suivi',
      onPress: onNavigateRecentRequests,
    },
  ];

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'المساعدة والدعم' : 'Aide & Support'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Navigation Cards */}
        {navCards.map((card) => (
          <TouchableOpacity
            key={card.id}
            style={[styles.navCard, isRTL && styles.rtlRow]}
            onPress={card.onPress}
            activeOpacity={0.7}
          >
            <View style={[styles.navIconBox, { backgroundColor: `${String(card.color)}15` }]}>
              <MayushIcon name={(card.icon || 'help-circle') as MayushIconName} size={22} color={card.color} />
            </View>
            <View style={styles.navTextCol}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {card.label}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {card.sub}
              </MayushText>
            </View>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
          </TouchableOpacity>
        ))}

        {/* FAQ Categories Quick Access */}
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionLabel, isRTL && styles.rtlText]}>
          {isRTL ? 'فئات الأسئلة الشائعة' : 'Catégories FAQ'}
        </MayushText>

        <View style={styles.categoryGrid}>
          {categories.map((cat) => (
            <TouchableOpacity
              key={cat.id}
              style={styles.categoryCard}
              onPress={() => {
                supportState.setSelectedFaqCategory(cat.id);
                onNavigateFaq?.();
              }}
              activeOpacity={0.7}
            >
              <View style={styles.categoryIconBox}>
                <MayushIcon name={(cat.icon || 'help-circle') as MayushIconName} size={20} color={colors.brand.orange500} />
              </View>
              <MayushText variant="smallBody" color={colors.brand.navy900} style={{ textAlign: 'center' }}>
                {isRTL ? cat.labelAr : cat.label}
              </MayushText>
            </TouchableOpacity>
          ))}
        </View>

        {/* Contact Channels */}
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionLabel, isRTL && styles.rtlText]}>
          {isRTL ? 'تواصل معنا' : 'Contactez-nous'}
        </MayushText>

        {channels.map((ch) => (
          <TouchableOpacity
            key={ch.id}
            style={[styles.channelRow, isRTL && styles.rtlRow]}
            onPress={() => handleChannelPress(ch)}
            activeOpacity={0.7}
          >
            <View style={styles.channelIconBox}>
              <MayushIcon name={(ch.icon || 'help-circle') as MayushIconName} size={20} color={colors.brand.orange500} />
            </View>
            <View style={styles.channelTextCol}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {isRTL ? ch.labelAr : ch.label}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {ch.value}
              </MayushText>
            </View>
            <MayushIcon name="external-link" size={16} color={colors.neutral.gray500} />
          </TouchableOpacity>
        ))}
      </ScrollView>

      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.neutral.gray100 },
  header: {
    height: 56, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: spacing.md, backgroundColor: colors.neutral.white,
    borderBottomWidth: 1, borderBottomColor: colors.neutral.gray300,
  },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  backButton: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  scrollContent: { padding: spacing.md, gap: spacing.sm, paddingBottom: 100 },
  navCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.md,
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  navIconBox: {
    width: 48, height: 48, borderRadius: 14, alignItems: 'center', justifyContent: 'center',
  },
  navTextCol: { flex: 1 },
  sectionLabel: { fontSize: 16, fontWeight: '700', marginTop: spacing.sm },
  categoryGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
  categoryCard: {
    width: '30%', backgroundColor: colors.neutral.white, borderRadius: 14,
    padding: spacing.sm, alignItems: 'center', gap: spacing.xs,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  categoryIconBox: {
    width: 40, height: 40, borderRadius: 10, backgroundColor: 'rgba(217,116,52,0.08)',
    alignItems: 'center', justifyContent: 'center',
  },
  channelRow: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.md,
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  channelIconBox: {
    width: 44, height: 44, borderRadius: 12, backgroundColor: 'rgba(217,116,52,0.1)',
    alignItems: 'center', justifyContent: 'center',
  },
  channelTextCol: { flex: 1 },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
