import React, { useMemo, useState } from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

const fallbackGalleryImages = [
  require('../../../assets/reference-art/home-new-luna.png'),
  require('../../../assets/reference-art/home-new-nori.png'),
  require('../../../assets/reference-art/home-new-kyoto.png'),
  require('../../../assets/reference-art/home-new-eve.png'),
  require('../../../assets/reference-art/home-best-linea.png'),
];

export interface ProductGalleryScreenProps {
  onBack: () => void;
  onNavigateTab: (tab: TabKey) => void;
  activeTab?: TabKey;
  images?: string[];
}

export const ProductGalleryScreen: React.FC<ProductGalleryScreenProps> = ({
  onBack,
  onNavigateTab,
  activeTab = 'home',
  images,
}) => {
  const { isRTL, language } = useTheme();
  const [activeImage, setActiveImage] = useState(0);
  const [isZoomed, setIsZoomed] = useState(false);

  const useNetworkImages = images && images.length > 0;
  const galleryImages: Array<{ uri: string } | ReturnType<typeof require>> = useNetworkImages
    ? images.map((url) => ({ uri: url }))
    : fallbackGalleryImages;
  const copy = useMemo(() => language === 'ar'
    ? {
      title: '\u0645\u0639\u0631\u0636 \u0627\u0644\u0645\u0646\u062a\u062c',
      subtitle: '\u0643\u0631\u0633\u064a \u0641\u0627\u062e\u0631 \u2013 \u0645\u062e\u0645\u0644 \u0643\u0631\u064a\u0645\u064a',
      zoom: '\u0627\u0644\u0636\u063a\u0637 \u0644\u0644\u062a\u0643\u0628\u064a\u0631',
      advice: '\u0646\u0635\u064a\u062d\u0629 Mayush',
      hint: '\u0627\u0633\u062a\u062e\u062f\u0645 \u0627\u0644\u062a\u0643\u0628\u064a\u0631 \u0644\u0645\u0639\u0627\u064a\u0646\u0629 \u062a\u0641\u0627\u0635\u064a\u0644 \u0627\u0644\u0642\u0645\u0627\u0634 \u0648\u0627\u0644\u0645\u0644\u0645\u0633 \u0628\u0623\u0641\u0636\u0644 \u0648\u0636\u0648\u062d.',
    }
    : {
      title: 'Galerie du produit',
      subtitle: 'Fauteuil Premium – Velours Crème',
      zoom: 'Appuyez pour zoomer',
      advice: 'Conseil Mayush',
      hint: 'Utilisez le zoom pour apprécier les détails et la texture haut de gamme.',
    }, [language]);

  const directionStyle = isRTL ? styles.rowReverse : undefined;

  return (
    <View style={styles.screen} accessibilityLabel={copy.title}>
      <View style={[styles.header, directionStyle]}>
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={isRTL ? '\u0631\u062c\u0648\u0639' : 'Retour'} onPress={onBack} style={styles.headerButton}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={25} color={colors.surface.white} strokeWidth={2.25} />
        </TouchableOpacity>
        <MayushLogo width={130} height={38} />
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={isRTL ? '\u0645\u0634\u0627\u0631\u0643\u0629' : 'Partager'} style={styles.shareButton}>
          <MayushIcon name="share" size={22} color={colors.brand.navy900} strokeWidth={2} />
        </TouchableOpacity>
      </View>

      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <View style={[styles.titleRow, directionStyle]}>
          <View style={styles.titleCopy}>
            <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>{copy.title}</MayushText>
            <MayushText variant="body" color={colors.neutral.gray700} style={[styles.subtitle, isRTL && styles.rtlText]}>{copy.subtitle}</MayushText>
          </View>
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={isRTL ? '\u0625\u0636\u0627\u0641\u0629 \u0625\u0644\u0649 \u0627\u0644\u0645\u0641\u0636\u0644\u0629' : 'Ajouter aux favoris'} style={styles.favoriteButton}>
            <MayushIcon name="heart" size={25} color={colors.brand.orange500} strokeWidth={2.1} />
          </TouchableOpacity>
        </View>

        <TouchableOpacity activeOpacity={0.96} accessibilityRole="imagebutton" accessibilityLabel={copy.zoom} onPress={() => setIsZoomed((value) => !value)} style={styles.heroFrame}>
          <Image source={galleryImages[activeImage]} resizeMode="cover" style={[styles.heroImage, isZoomed && styles.heroImageZoomed]} />
          <View style={[styles.imageCount, isRTL && styles.countRTL]}><MayushText variant="caption" color={colors.surface.white} style={styles.imageCountText}>{activeImage + 1} / {galleryImages.length}</MayushText></View>
          <View style={[styles.zoomHint, isRTL && styles.zoomHintRTL]}><MayushIcon name="search" size={15} color={colors.brand.navy900} /><MayushText variant="caption" color={colors.brand.navy900} style={styles.zoomText}>{copy.zoom}</MayushText></View>
        </TouchableOpacity>

        <View style={[styles.thumbnailRow, directionStyle]}>
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={isRTL ? '\u0627\u0644\u0635\u0648\u0631\u0629 \u0627\u0644\u0633\u0627\u0628\u0642\u0629' : 'Image précédente'} onPress={() => setActiveImage((value) => (value + galleryImages.length - 1) % galleryImages.length)} style={styles.thumbArrow}>
            <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={20} color={colors.brand.navy900} />
          </TouchableOpacity>
          <ScrollView horizontal={!isRTL} showsHorizontalScrollIndicator={false} contentContainerStyle={[styles.thumbnails, isRTL && styles.thumbnailRTL]}>
            {galleryImages.map((source, index) => (
              <TouchableOpacity key={index} accessibilityRole="button" accessibilityState={{ selected: activeImage === index }} accessibilityLabel={`${copy.title} ${index + 1}`} onPress={() => { setActiveImage(index); setIsZoomed(false); }} style={[styles.thumbnail, activeImage === index && styles.thumbnailActive]}>
                <Image source={source} resizeMode="cover" style={styles.thumbnailImage} />
              </TouchableOpacity>
            ))}
          </ScrollView>
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={isRTL ? '\u0627\u0644\u0635\u0648\u0631\u0629 \u0627\u0644\u062a\u0627\u0644\u064a\u0629' : 'Image suivante'} onPress={() => setActiveImage((value) => (value + 1) % galleryImages.length)} style={styles.thumbArrow}>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.brand.navy900} />
          </TouchableOpacity>
        </View>

        <View style={[styles.tipCard, directionStyle]}>
          <View style={styles.infoIcon}><MayushText variant="strongBody" color={colors.surface.white}>i</MayushText></View>
          <View style={styles.tipCopy}>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>{copy.advice}</MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray700} style={[styles.tipHint, isRTL && styles.rtlText]}>{copy.hint}</MayushText>
          </View>
          <MayushIcon name="share" size={29} color={colors.brand.orange500} strokeWidth={1.5} />
        </View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white },
  header: { height: 70, paddingHorizontal: 16, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', borderBottomWidth: 1, borderBottomColor: colors.surface.borderWarm, backgroundColor: colors.surface.creamLight },
  rowReverse: { flexDirection: 'row-reverse' },
  headerButton: { width: 34, height: 34, borderRadius: 17, backgroundColor: colors.brand.navy900, alignItems: 'center', justifyContent: 'center' },
  shareButton: { width: 34, height: 34, borderRadius: 17, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.surface.white, shadowColor: colors.brand.navy900, shadowOpacity: 0.11, shadowRadius: 7, shadowOffset: { width: 0, height: 2 }, elevation: 2 },
  content: { paddingTop: 21, paddingBottom: 12 },
  titleRow: { marginHorizontal: 16, flexDirection: 'row', alignItems: 'center', gap: 10 },
  titleCopy: { flex: 1 },
  title: { fontSize: 20, lineHeight: 26 },
  subtitle: { marginTop: 2, fontSize: 13, lineHeight: 19 },
  favoriteButton: { width: 36, height: 36, borderRadius: 18, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.surface.white, shadowColor: colors.brand.navy900, shadowOpacity: 0.1, shadowRadius: 7, shadowOffset: { width: 0, height: 2 }, elevation: 2 },
  heroFrame: { height: 352, marginHorizontal: 16, marginTop: 17, borderRadius: 10, overflow: 'hidden', backgroundColor: colors.surface.cream, position: 'relative' },
  heroImage: { width: '100%', height: '100%' },
  heroImageZoomed: { transform: [{ scale: 1.18 }] },
  imageCount: { position: 'absolute', left: 12, bottom: 13, minWidth: 49, height: 25, paddingHorizontal: 8, borderRadius: 13, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.brand.navy900 },
  countRTL: { left: undefined, right: 12 },
  imageCountText: { fontWeight: '700', fontSize: 10 },
  zoomHint: { position: 'absolute', right: 12, bottom: 13, height: 25, flexDirection: 'row', alignItems: 'center', gap: 5, borderRadius: 13, paddingHorizontal: 10, backgroundColor: 'rgba(255,255,255,0.94)' },
  zoomHintRTL: { right: undefined, left: 12, flexDirection: 'row-reverse' },
  zoomText: { fontSize: 10 },
  thumbnailRow: { marginTop: 10, marginHorizontal: 8, flexDirection: 'row', alignItems: 'center', gap: 6 },
  thumbnails: { flexGrow: 1, gap: 6, paddingHorizontal: 1 },
  thumbnailRTL: { flexDirection: 'row-reverse' },
  thumbArrow: { width: 26, height: 26, borderRadius: 13, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.surface.white, shadowColor: colors.brand.navy900, shadowOpacity: 0.1, shadowRadius: 5, shadowOffset: { width: 0, height: 2 }, elevation: 2 },
  thumbnail: { width: 58, height: 58, padding: 2, borderWidth: 2, borderColor: 'transparent', borderRadius: 7, overflow: 'hidden' },
  thumbnailActive: { borderColor: colors.brand.orange500 },
  thumbnailImage: { width: '100%', height: '100%', borderRadius: 3 },
  tipCard: { minHeight: 67, marginHorizontal: 16, marginTop: 15, flexDirection: 'row', alignItems: 'center', gap: 12, borderRadius: 10, paddingHorizontal: 14, paddingVertical: 10, backgroundColor: '#FEF7EF' },
  infoIcon: { width: 30, height: 30, borderRadius: 15, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.brand.orange500 },
  tipCopy: { flex: 1 },
  tipHint: { marginTop: 2, lineHeight: 16 },
  rtlText: { writingDirection: 'rtl', textAlign: 'right' },
});
