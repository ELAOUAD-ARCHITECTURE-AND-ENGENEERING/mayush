/**
 * ProductDeliveryReturnsScreen (Figma Node 309:609 - 03-product-delivery-returns-info)
 * Delivery options, coverage regions, free shipping conditions, and 14-day return policy.
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

export interface ProductDeliveryReturnsScreenProps {
  onBack: () => void;
}

export const ProductDeliveryReturnsScreen: React.FC<ProductDeliveryReturnsScreenProps> = ({
  onBack,
}) => {
  const { isRTL, language } = useTheme();

  return (
    <View style={styles.screen} accessibilityLabel="Product Delivery and Returns Screen">
      <View style={styles.header}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.iconBtn}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'التوصيل والإرجاع' : 'Livraison & Retours'}
        </MayushText>
        <View style={styles.iconBtn} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.card}>
          <View style={styles.cardHeaderRow}>
            <MayushIcon name="truck" size={24} color={colors.brand.orange500} />
            <MayushText variant="sectionTitle" color={colors.brand.navy900}>
              {language === 'ar' ? 'خيارات التوصيل بالمغرب' : 'Modes de livraison au Maroc'}
            </MayushText>
          </View>

          <View style={styles.optionBox}>
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {language === 'ar' ? 'توصيل قياسي (3 إلى 5 أيام)' : 'Livraison Standard (3-5 jours)'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray700} style={styles.desc}>
              {language === 'ar'
                ? 'مجاني للطلبات فوق 3 000 درهم. 150 درهم للطلبات الأخرى.'
                : 'Gratuite dès 3 000 MAD d’achat. 150 MAD pour les commandes inférieures.'}
            </MayushText>
          </View>

          <View style={styles.optionBox}>
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {language === 'ar' ? 'توصيل سريع (24 إلى 48 ساعة)' : 'Livraison Express (24-48h)'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray700} style={styles.desc}>
              {language === 'ar'
                ? 'متوفر في الدار البيضاء والرباط ومراكش. 300 درهم.'
                : 'Disponible à Casablanca, Rabat et Marrakech. 300 MAD.'}
            </MayushText>
          </View>
        </View>

        <View style={styles.card}>
          <View style={styles.cardHeaderRow}>
            <MayushIcon name="refresh-cw" size={24} color={colors.brand.orange500} />
            <MayushText variant="sectionTitle" color={colors.brand.navy900}>
              {language === 'ar' ? 'سياسة الإرجاع والاستبدال' : 'Politique de retour (14 jours)'}
            </MayushText>
          </View>

          <MayushText variant="body" color={colors.neutral.gray700} style={styles.paragraph}>
            {language === 'ar'
              ? 'يمكنك إرجاع المنتج خلال 14 يوماً من الاستلام بشرط أن يكون غير مستخدم وفي تغليفه الأصلي.'
              : 'Vous disposez d’un délai de 14 jours à compter de la réception de votre commande pour demander un retour ou un échange. Le produit doit être neuf, non utilisé et dans son emballage d’origine.'}
          </MayushText>
        </View>

        <View style={styles.card}>
          <View style={styles.cardHeaderRow}>
            <MayushIcon name="shield-check" size={24} color={colors.brand.orange500} />
            <MayushText variant="sectionTitle" color={colors.brand.navy900}>
              {language === 'ar' ? 'الضمان وخدمة ما بعد البيع' : 'Garantie & Service Après-Vente'}
            </MayushText>
          </View>
          <MayushText variant="body" color={colors.neutral.gray700} style={styles.paragraph}>
            {language === 'ar'
              ? 'جميع أثاثنا مغطى بضمان لمدة سنتين ضد عيوب التصنيع. فريقنا متاح 7 أيام في الأسبوع.'
              : 'Tous nos meubles bénéficient d’une garantie constructeur de 2 ans. Notre service client basé au Maroc est à votre disposition 7j/7.'}
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
  card: {
    padding: 16,
    borderRadius: radii.xl,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    marginBottom: 16,
  },
  cardHeaderRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 12, gap: 10 },
  optionBox: {
    padding: 12,
    borderRadius: radii.lg,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    marginBottom: 8,
  },
  desc: { marginTop: 4, lineHeight: 18 },
  paragraph: { lineHeight: 20 },
});
