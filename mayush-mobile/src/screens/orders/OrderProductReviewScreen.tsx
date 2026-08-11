import React, { useState } from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { ProductReviewDraftEntry } from '../../commerce/orderActionState';
import { BuyerOrder } from '../../commerce/orderState';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { getOrderLineImage, OrderActionButton, OrderCard, OrderScreenHeader } from './OrderScreenComponents';

export interface OrderProductReviewScreenProps {
  order: BuyerOrder;
  entries: ProductReviewDraftEntry[];
  onBack: () => void;
  onRate: (orderLineId: string, rating: number) => void;
  onSubmit: () => Promise<boolean>;
  onLater: () => void;
}

export const OrderProductReviewScreen: React.FC<OrderProductReviewScreenProps> = ({
  order, entries, onBack, onRate, onSubmit, onLater,
}) => {
  const { language, isRTL } = useTheme();
  const [showError, setShowError] = useState(false);
  const [submittedLocally, setSubmittedLocally] = useState(false);
  const copy = language === 'ar' ? {
    title: 'كيف كان طلبك؟', subtitle: 'تم تسليم طلبك. شارك تقييمك لكل منتج.', helper: 'شارك تجربتك مع هذا المنتج', infoTitle: 'رأيك مهم', info: 'تُحفظ التقييمات محلياً في هذا النموذج ولا يتم نشرها على الخادم.', publish: 'حفظ تقييماتي', later: 'لاحقاً', required: 'يرجى تقييم كل منتج قبل الحفظ.', saved: 'تم حفظ تقييماتك محلياً.',
  } : {
    title: 'Comment s’est passée votre commande ?', subtitle: 'Votre commande a été livrée. Partagez votre avis sur chaque article.', helper: 'Partagez votre expérience sur ce produit', infoTitle: 'Votre avis compte !', info: 'Dans ce prototype, les évaluations sont conservées localement et ne sont pas publiées sur un serveur.', publish: 'Enregistrer mes avis', later: 'Plus tard', required: 'Évaluez chaque produit avant d’enregistrer.', saved: 'Vos avis ont été enregistrés localement.',
  };
  const submit = async () => {
    const accepted = await onSubmit();
    setShowError(!accepted);
    setSubmittedLocally(accepted);
  };
  return (
    <View style={styles.screen} accessibilityLabel={`${copy.title} ${order.orderId}`}>
      <View style={styles.canvas}>
        <OrderScreenHeader onBack={onBack} title={copy.title} subtitle={copy.subtitle} />
        <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
          <View style={styles.hero}><MayushIcon name="star-filled" size={48} color={colors.brand.orange500} /></View>
          <OrderCard>
            {order.lines.map((line) => {
              const rating = entries.find((entry) => entry.orderLineId === line.orderLineId)?.rating || 0;
              return (
                <View key={line.orderLineId} style={[styles.product, isRTL && styles.rowReverse]}>
                  <Image source={getOrderLineImage(line)} style={styles.image} />
                  <View style={styles.productCopy}>
                    <MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{line.name}</MayushText>
                    <MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{copy.helper}</MayushText>
                    <View style={[styles.stars, isRTL && styles.rowReverse]}>
                      {[1, 2, 3, 4, 5].map((value) => (
                        <TouchableOpacity key={value} accessibilityRole="button" accessibilityLabel={`${value}/5`} onPress={() => { onRate(line.orderLineId, value); setShowError(false); }} style={styles.starButton}>
                          <MayushIcon name={value <= rating ? 'star-filled' : 'star'} size={25} color={colors.brand.orange500} />
                        </TouchableOpacity>
                      ))}
                    </View>
                  </View>
                </View>
              );
            })}
          </OrderCard>
          <OrderCard>
            <View style={[styles.info, isRTL && styles.rowReverse]}><View style={styles.infoIcon}><MayushIcon name="edit-3" size={27} color={colors.brand.navy900} /></View><View style={styles.flex}><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{copy.infoTitle}</MayushText><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{copy.info}</MayushText></View></View>
          </OrderCard>
          {showError ? <MayushText variant="caption" color={colors.semantic.error} align="center">{copy.required}</MayushText> : null}
          {submittedLocally ? <MayushText variant="caption" color={colors.semantic.success} align="center">{copy.saved}</MayushText> : null}
          <OrderActionButton label={copy.publish} icon="file-text" onPress={() => { void submit(); }} primary />
          <OrderActionButton label={copy.later} icon="clock" onPress={onLater} />
        </ScrollView>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFFDF9' }, canvas: { flex: 1, width: '100%', maxWidth: 393, alignSelf: 'center' }, content: { padding: 14, paddingBottom: 30, gap: 9 }, flex: { flex: 1 }, rowReverse: { flexDirection: 'row-reverse' },
  hero: { width: 108, height: 92, borderRadius: 26, alignSelf: 'center', alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF3E7' },
  product: { minHeight: 108, paddingVertical: 9, borderBottomWidth: 1, borderBottomColor: colors.surface.borderWarm, flexDirection: 'row', alignItems: 'center', gap: 11 }, image: { width: 82, height: 78, borderRadius: 8, backgroundColor: colors.surface.cream }, productCopy: { flex: 1, gap: 3 },
  stars: { flexDirection: 'row', marginTop: 3 }, starButton: { width: 29, minHeight: 32, alignItems: 'center', justifyContent: 'center' }, info: { flexDirection: 'row', alignItems: 'center', gap: 11 }, infoIcon: { width: 56, height: 56, borderRadius: 28, alignItems: 'center', justifyContent: 'center', backgroundColor: '#EDF3F8' },
});
