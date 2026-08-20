/**
 * ProductFullDescriptionScreen (Figma Node 309:606 - 03-product-detail-full-description-reviews-specs)
 * Full product craftsmanship description, design story, dimensions, and care guide.
 */

import React from 'react';
import {
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  View,
} from 'react-native';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface ProductFullDescriptionScreenProps {
  productTitle?: string;
  productName?: string;
  description?: string;
  features?: string[];
  onBack: () => void;
}

export const ProductFullDescriptionScreen: React.FC<ProductFullDescriptionScreenProps> = ({
  productTitle = 'Fauteuil Lounge Luna',
  productName,
  description,
  features: featuresProp,
  onBack,
}) => {
  const { isRTL, language } = useTheme();

  const defaultFeatures = [
    language === 'ar' ? 'هيكل من الخشب الصلب المعالج' : 'Structure en bois massif de hêtre certifié FSC.',
    language === 'ar' ? 'قماش بوقلي عالي الجودة مقاوِم للبقع' : 'Revêtement tissu bouclé premium résistant aux taches.',
    language === 'ar' ? 'حشوة رغوية عالية الكثافة (35 كجم/م³)' : 'Mousse haute résilience 35 kg/m³ pour un confort durable.',
    language === 'ar' ? 'تصميم مريح لدعم أسفل الظهر' : 'Ergonomie étudiée pour un soutien lombaire parfait.',
  ];

  const resolvedTitle = productName ?? productTitle;
  const features = featuresProp && featuresProp.length > 0 ? featuresProp : defaultFeatures;

  return (
    <View style={styles.screen} accessibilityLabel="Product Full Description Screen">
      <View style={styles.header}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.iconBtn}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'الوصف التفصيلي' : 'Description détaillée'}
        </MayushText>
        <View style={styles.iconBtn} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.prodTitle}>
          {resolvedTitle}
        </MayushText>

        <View style={styles.card}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionHeading}>
            {language === 'ar' ? 'قصة التصميم والحرفية' : 'Histoire du Design & Artisanat'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray700} style={styles.paragraph}>
            {description ?? (language === 'ar'
              ? 'تم تصميم هذا الكرسي ليكون قطعة مركزية في صالونك الحديث. يجمع بين البساطة السكاندينافية ولمسات الحرفية المغربية العصرية.'
              : 'Imaginé par les artisans Mayush Design, le fauteuil Luna allie rondeur contemporaine et confort enveloppant. Ses lignes douces et son tissu bouclé naturel apportent une touche chaleureuse et raffinée à votre pièce de vie.')}
          </MayushText>
        </View>

        <View style={styles.card}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionHeading}>
            {language === 'ar' ? 'الميزات الرئيسية' : 'Points forts du produit'}
          </MayushText>
          {features.map((feat, idx) => (
            <View key={idx} style={styles.featureRow}>
              <MayushIcon name="check-circle" size={18} color={colors.brand.orange500} />
              <MayushText variant="body" color={colors.brand.navy900} style={styles.featureText}>
                {feat}
              </MayushText>
            </View>
          ))}
        </View>

        <View style={styles.card}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionHeading}>
            {language === 'ar' ? 'إرشادات العناية' : 'Conseils d’entretien'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray700} style={styles.paragraph}>
            {language === 'ar'
              ? 'يُنصح بالتنظيف الجاف أو استخدام قطعة قماش ناعمة مبللة بالماء الفاتر لتنظيف البقع الخفيفة.'
              : 'Dépoussiérer régulièrement à l’aide d’une brosse douce. En cas de tache, tamponner immédiatement avec un chiffon propre et légèrement humide.'}
          </MayushText>
        </View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white },
  header: {
    height: 64,
    paddingHorizontal: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderBottomWidth: 1,
    borderBottomColor: colors.surface.borderWarm,
  },
  iconBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  content: { padding: 20 },
  prodTitle: { fontSize: 22, marginBottom: 16 },
  card: {
    padding: 16,
    borderRadius: radii.xl,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    marginBottom: 16,
  },
  sectionHeading: { fontSize: 17, marginBottom: 10 },
  paragraph: { lineHeight: 22 },
  featureRow: { flexDirection: 'row', alignItems: 'flex-start', marginTop: 8, gap: 10 },
  featureText: { flex: 1, lineHeight: 20 },
});
