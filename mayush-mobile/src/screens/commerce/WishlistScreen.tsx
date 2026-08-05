import React from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

const RECOMMENDATIONS = [
  { name: 'Canap\u00e9s', count: '23 produits', image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=350&auto=format&fit=crop' },
  { name: '\u00c9tables \u00e0 manger', count: '18 produits', image: 'https://images.unsplash.com/photo-1615066390971-03e4e1c36ddf?q=80&w=350&auto=format&fit=crop' },
  { name: 'Buffets & rangements', count: '21 produits', image: 'https://images.unsplash.com/photo-1595428774223-ef52624120d2?q=80&w=350&auto=format&fit=crop' },
  { name: 'Fauteuils', count: '16 produits', image: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?q=80&w=350&auto=format&fit=crop' },
];
const WISHLIST_ARTWORK = require('../../../assets/illustrations/wishlist-empty-scene.png');

export interface WishlistScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBrowseCollections?: () => void;
}

export const WishlistScreen: React.FC<WishlistScreenProps> = ({ onNavigateTab, onBrowseCollections }) => {
  const { isRTL, language } = useTheme();
  const copy = language === 'ar'
    ? { title: '\u0645\u0641\u0636\u0644\u062a\u064a', empty: '\u0642\u0627\u0626\u0645\u0629 \u0631\u063a\u0628\u0627\u062a\u0643 \u0641\u0627\u0631\u063a\u0629', body: '\u0627\u062d\u0641\u0638 \u0627\u0644\u0623\u062b\u0627\u062b \u0648\u0627\u0644\u062f\u064a\u0643\u0648\u0631 \u0627\u0644\u0630\u064a \u062a\u062d\u0628\u0647 \u0644\u062a\u062c\u062f\u0647 \u0647\u0646\u0627.', cta: '\u0627\u0643\u062a\u0634\u0641 \u0627\u0644\u0645\u062c\u0645\u0648\u0639\u0627\u062a', section: '\u0627\u0643\u062a\u0634\u0641 \u0627\u062e\u062a\u064a\u0627\u0631\u0627\u062a\u0646\u0627', all: '\u0639\u0631\u0636 \u0627\u0644\u0643\u0644' }
    : { title: 'Mes favoris', empty: 'Votre liste d\u2019envies est vide', body: 'Enregistrez vos meubles et d\u00e9corations pr\u00e9f\u00e9r\u00e9s\npour les retrouver facilement ici.', cta: 'D\u00e9couvrir les collections', section: 'D\u00e9couvrez nos s\u00e9lections', all: 'Voir tout' };

  return (
    <View style={styles.screen} accessibilityLabel={copy.title}>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <View style={[styles.header, isRTL && styles.rowReverse]}>
          <MayushLogo width={145} height={43} />
          <View style={[styles.headerActions, isRTL && styles.rowReverse]}>
            <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.cta} onPress={onBrowseCollections} style={styles.iconButton}><MayushIcon name="search" size={25} color={colors.brand.navy900} /></TouchableOpacity>
            <View style={styles.iconButton}><MayushIcon name="bell" size={25} color={colors.brand.navy900} /></View>
          </View>
        </View>
        <MayushText variant="display" color={colors.brand.navy900} style={[styles.pageTitle, isRTL && styles.rtlText]}>{copy.title}</MayushText>

        <View pointerEvents="none" style={styles.emptyBlock}><Image source={WISHLIST_ARTWORK} resizeMode="contain" style={styles.referenceArtwork} /></View>
        <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={[styles.emptyTitle, isRTL && styles.rtlText]}>{copy.empty}</MayushText>
        <MayushText variant="body" color={colors.neutral.gray700} align="center" style={[styles.emptyCopy, isRTL && styles.rtlText]}>{copy.body}</MayushText>
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.cta} activeOpacity={0.84} onPress={onBrowseCollections} style={styles.primaryButton}>
          <MayushText variant="button" color={colors.surface.white} style={styles.primaryLabel}>{copy.cta}</MayushText>
          <MayushIcon name={isRTL ? 'arrow-left' : 'arrow-right'} size={25} color={colors.surface.white} />
        </TouchableOpacity>

        <View style={[styles.sectionHeader, isRTL && styles.rowReverse]}><MayushText variant="sectionTitle" color={colors.brand.navy900}>{copy.section}</MayushText><TouchableOpacity onPress={onBrowseCollections} style={styles.allButton}><MayushText variant="body" color={colors.brand.orange500} style={styles.allLabel}>{copy.all}</MayushText><MayushIcon name={isRTL ? 'arrow-left' : 'arrow-right'} size={19} color={colors.brand.orange500} /></TouchableOpacity></View>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.recommendations}>
          {RECOMMENDATIONS.map((item) => <TouchableOpacity key={item.name} accessibilityRole="button" accessibilityLabel={item.name} activeOpacity={0.82} onPress={onBrowseCollections} style={styles.recommendationCard}><Image source={{ uri: item.image }} style={styles.recommendationImage} /><MayushText variant="smallBody" color={colors.brand.navy900} numberOfLines={1} style={styles.recommendationName}>{item.name}</MayushText><MayushText variant="caption" color={colors.neutral.gray700}>{item.count}</MayushText></TouchableOpacity>)}
        </ScrollView>
        <View style={styles.benefitBar}>
          <Benefit icon="heart" label={language === 'ar' ? '\u0645\u0641\u0636\u0644\u062a\u0643\n\u0641\u064a \u0645\u0643\u0627\u0646 \u0648\u0627\u062d\u062f' : 'Vos coups de c\u0153ur\nau m\u00eame endroit'} />
          <Benefit icon="bell" label={language === 'ar' ? '\u062a\u0644\u0642\u0651 \u0625\u0634\u0639\u0627\u0631\u0627\u062a\n\u0627\u0644\u0623\u0633\u0639\u0627\u0631' : 'Soyez notifi\u00e9 des\nbaisses de prix'} />
          <Benefit icon="shopping-bag" label={language === 'ar' ? '\u0623\u0636\u0641 \u0625\u0644\u0649 \u0627\u0644\u0633\u0644\u0629\n\u0628\u0646\u0642\u0631\u0629' : 'Ajoutez au panier\nen un clic'} />
        </View>
      </ScrollView>
      <BottomTabBar activeTab="wishlist" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const Benefit: React.FC<{ icon: 'heart' | 'bell' | 'shopping-bag'; label: string }> = ({ icon, label }) => <View style={styles.benefit}><MayushIcon name={icon} size={27} color={colors.brand.orange500} /><MayushText variant="caption" color={colors.brand.navy900} style={styles.benefitLabel}>{label}</MayushText></View>;

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#F9F9F8' }, content: { paddingBottom: 26 },
  header: { minHeight: 78, paddingHorizontal: 22, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', borderBottomWidth: 1, borderBottomColor: '#EEE7DE' }, headerActions: { flexDirection: 'row', gap: 8 }, rowReverse: { flexDirection: 'row-reverse' }, iconButton: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  pageTitle: { marginTop: 25, marginHorizontal: 24, fontSize: 31, lineHeight: 37 }, rtlText: { writingDirection: 'rtl' },
  emptyBlock: { height: 195, marginTop: 16, alignItems: 'center', justifyContent: 'center' }, referenceArtwork: { width: '100%', height: '100%' },
  emptyTitle: { marginTop: 1, marginHorizontal: 22, fontSize: 25, lineHeight: 31 }, emptyCopy: { marginTop: 12, marginHorizontal: 30, fontSize: 16, lineHeight: 24 },
  primaryButton: { height: 58, marginHorizontal: 30, marginTop: 24, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 15, borderRadius: 16, backgroundColor: colors.brand.orange500, shadowColor: colors.brand.orange500, shadowOpacity: 0.24, shadowRadius: 10, shadowOffset: { width: 0, height: 6 }, elevation: 4 }, primaryLabel: { fontSize: 18, fontWeight: '700' },
  sectionHeader: { marginTop: 34, marginHorizontal: 24, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }, allButton: { flexDirection: 'row', alignItems: 'center', gap: 6 }, allLabel: { fontWeight: '700' }, recommendations: { paddingHorizontal: 24, paddingTop: 16, gap: 12 }, recommendationCard: { width: 132 }, recommendationImage: { width: 132, height: 104, borderRadius: 14, backgroundColor: '#F0E7DD' }, recommendationName: { marginTop: 8, fontWeight: '700' },
  benefitBar: { minHeight: 86, marginHorizontal: 22, marginTop: 27, paddingVertical: 11, flexDirection: 'row', borderRadius: 17, backgroundColor: '#FFF4E9' }, benefit: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: 5, paddingHorizontal: 4 }, benefitLabel: { textAlign: 'center', fontSize: 10, lineHeight: 13 },
});
