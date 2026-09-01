import React from 'react';
import {
  Image,
  ImageSourcePropType,
  StyleProp,
  StyleSheet,
  TouchableOpacity,
  View,
  ViewStyle,
} from 'react-native';
import { formatMadPrice } from '../../commerce/cartState';
import { DeliveryStatus, OrderLine } from '../../commerce/orderState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { Card } from '../../design-system/components/layout/Card';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

const PRODUCT_IMAGES: ImageSourcePropType[] = [
  require('../../../assets/reference-art/home-new-luna.png'),
  require('../../../assets/reference-art/home-new-nori.png'),
  require('../../../assets/reference-art/home-new-kyoto.png'),
  require('../../../assets/reference-art/home-new-eve.png'),
];

export const getOrderLineImage = (line: OrderLine): ImageSourcePropType => {
  if (line.imageUri) return { uri: line.imageUri };
  const numericId = typeof line.productId === 'number'
    ? line.productId
    : [...String(line.productId)].reduce((total, character) => total + character.charCodeAt(0), 0);
  return PRODUCT_IMAGES[Math.abs(numericId) % PRODUCT_IMAGES.length];
};

export const formatOrderDate = (value?: string, language: 'fr' | 'ar' = 'fr'): string => {
  if (!value) return language === 'ar' ? 'قادم' : 'À venir';
  try {
    return new Intl.DateTimeFormat(language === 'ar' ? 'ar-MA' : 'fr-MA', {
      dateStyle: 'medium',
      timeStyle: 'short',
    }).format(new Date(value));
  } catch {
    return value;
  }
};

export const getTrackingEventLabel = (labelKey: string, language: 'fr' | 'ar'): string => {
  const labels = language === 'ar' ? {
    confirmed: 'تم تأكيد الطلب', preparing: 'قيد التحضير', shipped: 'تم الشحن', handed_to_carrier: 'سُلّمت للناقل',
    in_transit: 'في الطريق', out_for_delivery: 'في طور التسليم', delivered: 'تم التسليم',
    preparation_complete: 'اكتمل التحضير', package_shipped: 'تم شحن الطرد',
  } : {
    confirmed: 'Commande confirmée', preparing: 'En préparation', shipped: 'Expédiée', handed_to_carrier: 'Remise au transporteur',
    in_transit: 'En transit', out_for_delivery: 'En cours de livraison', delivered: 'Livrée',
    preparation_complete: 'Préparation terminée', package_shipped: 'Colis expédié',
  };
  return labels[labelKey as keyof typeof labels] || labelKey;
};

export const OrderScreenHeader: React.FC<{
  onBack: () => void;
  showBell?: boolean;
  title: string;
  subtitle?: string;
}> = ({ onBack, showBell = true, title, subtitle }) => {
  const { isRTL } = useTheme();
  return (
    <View>
      <View style={[styles.header, isRTL && styles.rowReverse]}>
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={isRTL ? 'رجوع' : 'Retour'} onPress={onBack} style={styles.headerIcon}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={28} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushLogo width={132} height={42} />
        <View style={styles.headerIcon}>{showBell ? <MayushIcon name="bell" size={25} color={colors.brand.navy900} /> : null}</View>
      </View>
      <MayushText variant="display" color={colors.brand.navy900} align="center" style={styles.title}>{title}</MayushText>
      {subtitle ? <MayushText variant="body" color={colors.neutral.gray700} align="center" style={styles.subtitle}>{subtitle}</MayushText> : null}
    </View>
  );
};

export const OrderCard: React.FC<{ children: React.ReactNode; style?: StyleProp<ViewStyle> }> = ({ children, style }) => (
  <Card padding="md" radius="xl" shadow="sm" style={style}>{children}</Card>
);

export const OrderStatusBadge: React.FC<{ status: DeliveryStatus; label: string }> = ({ status, label }) => {
  const success = status === 'delivered' || status === 'in_transit';
  const neutral = status === 'shipped';
  return (
    <View style={[styles.badge, success && styles.badgeSuccess, neutral && styles.badgeInfo]}>
      <MayushIcon name={success ? 'check-circle' : status === 'preparing' ? 'clock' : 'truck-outline'} size={15} color={success ? colors.semantic.success : neutral ? colors.semantic.info : colors.brand.orange500} />
      <MayushText variant="caption" color={success ? colors.semantic.success : neutral ? colors.semantic.info : colors.brand.orange500}>{label}</MayushText>
    </View>
  );
};

export const OrderLineList: React.FC<{ lines: OrderLine[]; showPrices?: boolean }> = ({ lines, showPrices = true }) => {
  const { isRTL, language } = useTheme();
  return (
    <View style={styles.lineList}>
      {lines.map((line) => (
        <View key={line.orderLineId} style={[styles.line, isRTL && styles.rowReverse]}>
          <Image source={getOrderLineImage(line)} style={styles.lineImage} />
          <View style={styles.lineCopy}>
            <MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{line.name}</MayushText>
            <MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{line.variantLabel}</MayushText>
            <MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{language === 'ar' ? 'الكمية' : 'Qté'} : {line.quantity}</MayushText>
          </View>
          {showPrices ? <MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'left' : 'right'}>{formatMadPrice(line.unitPriceMad * line.quantity)}</MayushText> : null}
        </View>
      ))}
    </View>
  );
};

export const OrderActionButton: React.FC<{
  label: string;
  icon: MayushIconName;
  onPress: () => void;
  primary?: boolean;
  disabled?: boolean;
}> = ({ label, icon, onPress, primary = false, disabled = false }) => {
  const { isRTL } = useTheme();
  const foreground = primary ? colors.surface.white : colors.brand.navy900;
  return (
    <TouchableOpacity
      accessibilityRole="button"
      accessibilityState={{ disabled }}
      disabled={disabled}
      onPress={onPress}
      style={[styles.action, primary && styles.actionPrimary, disabled && styles.actionDisabled, isRTL && styles.rowReverse]}
    >
      <MayushIcon name={icon} size={20} color={foreground} />
      <MayushText variant="button" color={foreground} align="center" style={styles.actionLabel}>{label}</MayushText>
      <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={foreground} />
    </TouchableOpacity>
  );
};

export const getStatusCopy = (status: DeliveryStatus, language: 'fr' | 'ar'): string => {
  const labels = language === 'ar'
    ? { preparing: 'قيد التحضير', shipped: 'تم الشحن', in_transit: 'في الطريق', delivered: 'تم التسليم', cancelled: 'ملغى', returning: 'قيد الإرجاع', returned: 'تم الإرجاع' }
    : { preparing: 'En préparation', shipped: 'Expédiée', in_transit: 'En transit', delivered: 'Livrée', cancelled: 'Annulée', returning: 'Retour en cours', returned: 'Retournée' };
  return labels[status];
};

const styles = StyleSheet.create({
  header: { height: 70, paddingHorizontal: 20, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  headerIcon: { width: 32, minHeight: 32, alignItems: 'center', justifyContent: 'center' },
  title: { fontSize: 28, lineHeight: 34, paddingHorizontal: 18 },
  subtitle: { marginTop: 3, paddingHorizontal: 24, fontSize: 13, lineHeight: 18 },
  rowReverse: { flexDirection: 'row-reverse' },
  badge: { minHeight: 27, borderRadius: 7, paddingHorizontal: 9, flexDirection: 'row', alignItems: 'center', gap: 5, backgroundColor: '#FFF2E6' },
  badgeSuccess: { backgroundColor: colors.semantic.successBackground },
  badgeInfo: { backgroundColor: '#EAF1F7' },
  lineList: { gap: 9 },
  line: { minHeight: 66, flexDirection: 'row', alignItems: 'center', gap: 9, borderBottomWidth: 1, borderBottomColor: colors.surface.borderWarm, paddingBottom: 8 },
  lineImage: { width: 74, height: 58, borderRadius: 7, backgroundColor: colors.surface.cream },
  lineCopy: { flex: 1, gap: 2 },
  action: { minHeight: 47, borderRadius: 10, borderWidth: 1, borderColor: colors.brand.navy900, paddingHorizontal: 14, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 9, backgroundColor: colors.surface.white },
  actionPrimary: { borderColor: colors.brand.orange500, backgroundColor: colors.brand.orange500 },
  actionDisabled: { opacity: 0.55 },
  actionLabel: { flex: 1 },
});
