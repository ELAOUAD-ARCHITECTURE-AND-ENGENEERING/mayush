import React, { useState } from 'react';
import { ScrollView, StyleSheet, TextInput, TouchableOpacity, View } from 'react-native';
import { formatMadPrice } from '../../commerce/cartState';
import { CancellationDraft, CancellationReasonKey, CancellationRequestRecord } from '../../commerce/orderActionState';
import { BuyerOrder } from '../../commerce/orderState';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { OrderActionButton, OrderCard, OrderLineList, OrderScreenHeader } from './OrderScreenComponents';

interface BaseCancellationProps {
  order: BuyerOrder;
  onBack: () => void;
}

export const OrderCancellationConfirmationScreen: React.FC<BaseCancellationProps & {
  onContinue: () => void;
}> = ({ order, onBack, onContinue }) => {
  const { language, isRTL } = useTheme();
  const copy = language === 'ar' ? {
    title: 'تأكيد الإلغاء', subtitle: 'هل أنت متأكد من رغبتك في إلغاء هذا الطلب؟', warning: 'لا يتم إلغاء الطلب عند فتح هذه الصفحة. سيُرسل طلبك المحلي للمراجعة بعد اختيار السبب.', items: 'المنتجات المعنية', keep: 'الاحتفاظ بطلبـي', confirm: 'متابعة الإلغاء', footer: 'هذه محاكاة واجهة فقط ولم يتم إرسال أي طلب إلى الخادم.', reference: 'مرجع الطلب',
  } : {
    title: 'Confirmer l’annulation', subtitle: 'Êtes-vous sûr de vouloir annuler cette commande ?', warning: 'L’ouverture de cette page ne modifie pas la commande. Une demande locale sera préparée après le choix du motif.', items: 'Articles concernés', keep: 'Conserver ma commande', confirm: 'Continuer l’annulation', footer: 'Simulation frontend : aucune demande n’est encore envoyée au serveur.', reference: 'Référence de commande',
  };
  return (
    <Screen>
      <OrderScreenHeader onBack={onBack} title={copy.title} subtitle={copy.subtitle} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <HeroIcon icon="x" tone="warning" />
        <OrderCard><MetaRow label={copy.reference} value={order.orderId} isRTL={isRTL} /></OrderCard>
        <Warning text={copy.warning} isRTL={isRTL} />
        <OrderCard>
          <SectionTitle title={`${copy.items} (${order.lines.length})`} isRTL={isRTL} />
          <OrderLineList lines={order.lines} />
        </OrderCard>
        <OrderActionButton label={copy.keep} icon="shopping-bag" onPress={onBack} />
        <OrderActionButton label={copy.confirm} icon="x-circle" onPress={onContinue} primary />
        <MayushText variant="caption" color={colors.neutral.gray700} align="center">{copy.footer}</MayushText>
      </ScrollView>
    </Screen>
  );
};

const REASON_KEYS: CancellationReasonKey[] = [
  'ordered_by_mistake', 'modify_products', 'delivery_too_long', 'payment_problem', 'other',
];

export const OrderCancellationReasonScreen: React.FC<BaseCancellationProps & {
  draft: CancellationDraft;
  onReasonChange: (reason: CancellationReasonKey) => void;
  onMessageChange: (message: string) => void;
  onSubmit: () => Promise<boolean>;
}> = ({ order, draft, onBack, onReasonChange, onMessageChange, onSubmit }) => {
  const { language, isRTL } = useTheme();
  const [showError, setShowError] = useState(false);
  const reasons = language === 'ar' ? {
    ordered_by_mistake: 'تم تقديم الطلب عن طريق الخطأ', modify_products: 'أرغب في تعديل المنتجات', delivery_too_long: 'مدة التوصيل طويلة', payment_problem: 'مشكلة في الدفع', other: 'سبب آخر',
  } : {
    ordered_by_mistake: 'Commande passée par erreur', modify_products: 'Je souhaite modifier les produits', delivery_too_long: 'Délai trop long', payment_problem: 'Problème de paiement', other: 'Autre raison',
  };
  const copy = language === 'ar' ? {
    title: 'إلغاء الطلب', subtitle: 'سيتم تسجيل طلب الإلغاء محلياً للمراجعة.', reason: 'سبب الإلغاء', helper: 'يرجى اختيار سبب لطلبك.', message: 'رسالة (اختيارية)', placeholder: 'صف طلبك بإيجاز…', refund: 'معلومات الاسترداد', refundText: 'لا يبدأ هذا النموذج أي استرداد. سيتم عرض أي معالجة حقيقية فقط بعد تكامل الخادم.', conditions: 'يمكن تقديم طلب الإلغاء فقط ما دام الطلب في حالة التحضير.', submit: 'تسجيل الطلب', required: 'اختر سبباً قبل المتابعة.', reference: 'مرجع الطلب',
  } : {
    title: 'Annuler la commande', subtitle: 'Votre demande d’annulation sera enregistrée localement pour examen.', reason: 'Raison de l’annulation', helper: 'Veuillez sélectionner la raison de votre demande.', message: 'Message (optionnel)', placeholder: 'Décrivez brièvement votre demande…', refund: 'Information remboursement', refundText: 'Ce prototype ne déclenche aucun remboursement. Un traitement réel ne pourra être affiché qu’après intégration backend.', conditions: 'La demande est disponible uniquement tant que la commande est en préparation.', submit: 'Enregistrer la demande', required: 'Sélectionnez une raison avant de continuer.', reference: 'Référence de commande',
  };
  const submit = async () => {
    const accepted = await onSubmit();
    setShowError(!accepted);
  };
  return (
    <Screen>
      <OrderScreenHeader onBack={onBack} title={copy.title} subtitle={copy.subtitle} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <OrderCard><MetaRow label={copy.reference} value={order.orderId} isRTL={isRTL} /></OrderCard>
        <OrderCard>
          <SectionTitle title={copy.reason} subtitle={copy.helper} isRTL={isRTL} />
          <View style={styles.reasonList}>
            {REASON_KEYS.map((key) => {
              const selected = draft.reasonKey === key;
              return <TouchableOpacity key={key} accessibilityRole="radio" accessibilityState={{ checked: selected }} onPress={() => { onReasonChange(key); setShowError(false); }} style={[styles.reasonRow, selected && styles.reasonSelected, isRTL && styles.rowReverse]}><View style={[styles.radio, selected && styles.radioSelected]}>{selected ? <View style={styles.radioDot} /> : null}</View><MayushText variant="body" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'} style={styles.flex}>{reasons[key]}</MayushText></TouchableOpacity>;
            })}
          </View>
          {showError ? <MayushText variant="caption" color={colors.semantic.error} align={isRTL ? 'right' : 'left'}>{copy.required}</MayushText> : null}
        </OrderCard>
        <OrderCard>
          <SectionTitle title={copy.message} isRTL={isRTL} />
          <TextInput accessibilityLabel={copy.message} multiline maxLength={250} value={draft.message} onChangeText={onMessageChange} placeholder={copy.placeholder} placeholderTextColor={colors.neutral.gray500} textAlign={isRTL ? 'right' : 'left'} style={styles.input} />
          <MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'left' : 'right'}>{draft.message.length}/250</MayushText>
        </OrderCard>
        <InfoCard icon="wallet" title={copy.refund} text={copy.refundText} isRTL={isRTL} />
        <Warning text={copy.conditions} isRTL={isRTL} />
        <OrderActionButton label={copy.submit} icon="send" onPress={() => { void submit(); }} primary />
      </ScrollView>
    </Screen>
  );
};

export const OrderCancellationRegisteredScreen: React.FC<BaseCancellationProps & {
  request: CancellationRequestRecord;
  onOpenOrders: () => void;
  onContinueShopping: () => void;
}> = ({ order, request, onBack, onOpenOrders, onContinueShopping }) => {
  const { language, isRTL } = useTheme();
  const copy = language === 'ar' ? {
    title: 'تم تسجيل طلب الإلغاء', subtitle: 'تم حفظ طلبك على هذا الجهاز للمراجعة. لم يتم إرساله إلى الخادم.', status: 'حالة الطلب', requested: 'طلب إلغاء محلي', review: 'قيد المراجعة محلياً', reviewText: 'لم يتم قبول الإلغاء أو بدء أي استرداد. هذه حالة واجهة تجريبية فقط.', summary: 'ملخص الطلب', total: 'إجمالي الطلب', orders: 'عرض طلباتي', shop: 'متابعة التسوق',
  } : {
    title: 'Demande d’annulation enregistrée', subtitle: 'Votre demande est conservée sur cet appareil pour examen. Elle n’a pas été envoyée au serveur.', status: 'Statut de la demande', requested: 'Annulation demandée localement', review: 'Examen en attente', reviewText: 'L’annulation n’est pas acceptée et aucun remboursement n’est engagé. Il s’agit d’un état frontend.', summary: 'Résumé de la commande', total: 'Total de la commande', orders: 'Voir mes commandes', shop: 'Continuer mes achats',
  };
  return (
    <Screen>
      <OrderScreenHeader onBack={onBack} title={copy.title} subtitle={copy.subtitle} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <HeroIcon icon="check" tone="success" />
        <OrderCard>
          <MetaRow label={language === 'ar' ? 'رقم الطلب' : 'Numéro de commande'} value={order.orderId} isRTL={isRTL} />
          <View style={styles.separator} />
          <MetaRow label={copy.status} value={copy.requested} isRTL={isRTL} accent />
        </OrderCard>
        <InfoCard icon="info" title={copy.review} text={copy.reviewText} isRTL={isRTL} />
        <OrderCard>
          <SectionTitle title={copy.summary} subtitle={`${order.lines.length} articles`} isRTL={isRTL} />
          <OrderLineList lines={order.lines} />
          <View style={styles.totalRow}><MayushText variant="sectionTitle" color={colors.brand.navy900}>{copy.total}</MayushText><MayushText variant="sectionTitle" color={colors.brand.orange500}>{formatMadPrice(order.totalMad)}</MayushText></View>
        </OrderCard>
        <MayushText variant="caption" color={colors.neutral.gray700} align="center">{request.requestedAt}</MayushText>
        <OrderActionButton label={copy.orders} icon="clipboard" onPress={onOpenOrders} primary />
        <OrderActionButton label={copy.shop} icon="shopping-bag" onPress={onContinueShopping} />
      </ScrollView>
    </Screen>
  );
};

export const OrderCannotBeCancelledScreen: React.FC<BaseCancellationProps & {
  onSupport: () => void;
}> = ({ order, onBack, onSupport }) => {
  const { language, isRTL } = useTheme();
  const copy = language === 'ar' ? {
    title: 'لا يمكن إلغاء هذا الطلب', subtitle: 'الطلب في مرحلة شحن أو تسليم لا تسمح بمسار الإلغاء العادي.', status: 'حالة المعالجة', info: 'تستند هذه النتيجة إلى حالة الطلب الحالية ولا يتم إنشاء طلب وهمي مستقل.', back: 'العودة إلى الطلب', support: 'الاتصال بالدعم',
  } : {
    title: 'Cette commande ne peut plus être annulée', subtitle: 'La commande est à un stade d’expédition ou de livraison incompatible avec l’annulation normale.', status: 'Statut de traitement', info: 'Ce résultat provient de l’état actuel de la commande ; aucune commande fictive indépendante n’est créée.', back: 'Retour à la commande', support: 'Contacter le support',
  };
  return (
    <Screen>
      <OrderScreenHeader onBack={onBack} title={copy.title} subtitle={copy.subtitle} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <HeroIcon icon="box" tone="warning" />
        <OrderCard><MetaRow label={language === 'ar' ? 'رقم الطلب' : 'Numéro de commande'} value={order.orderId} isRTL={isRTL} /><View style={styles.separator} /><MetaRow label={copy.status} value={order.deliveryStatus} isRTL={isRTL} accent /></OrderCard>
        <OrderCard><OrderLineList lines={order.lines} /><View style={styles.totalRow}><MayushText variant="body" color={colors.neutral.gray700}>{language === 'ar' ? 'الإجمالي المدفوع' : 'Total payé'}</MayushText><MayushText variant="sectionTitle" color={colors.brand.orange500}>{formatMadPrice(order.totalMad)}</MayushText></View></OrderCard>
        <Warning text={copy.info} isRTL={isRTL} />
        <OrderActionButton label={copy.back} icon="arrow-left" onPress={onBack} primary />
        <OrderActionButton label={copy.support} icon="headphones" onPress={onSupport} />
      </ScrollView>
    </Screen>
  );
};

const Screen: React.FC<{ children: React.ReactNode }> = ({ children }) => <View style={styles.screen}><View style={styles.canvas}>{children}</View></View>;
const HeroIcon: React.FC<{ icon: 'x' | 'check' | 'box'; tone: 'warning' | 'success' }> = ({ icon, tone }) => <View style={[styles.hero, tone === 'success' && styles.heroSuccess]}><MayushIcon name={icon} size={48} color={tone === 'success' ? colors.semantic.success : colors.brand.orange500} /></View>;
const MetaRow: React.FC<{ label: string; value: string; isRTL: boolean; accent?: boolean }> = ({ label, value, isRTL, accent }) => <View style={[styles.metaRow, isRTL && styles.rowReverse]}><View style={styles.metaIcon}><MayushIcon name="clipboard" size={20} color={colors.brand.orange500} /></View><View style={styles.flex}><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{label.toUpperCase()}</MayushText><MayushText variant="strongBody" color={accent ? colors.brand.orange500 : colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{value}</MayushText></View></View>;
const SectionTitle: React.FC<{ title: string; subtitle?: string; isRTL: boolean }> = ({ title, subtitle, isRTL }) => <View style={styles.sectionTitle}><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{title}</MayushText>{subtitle ? <MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{subtitle}</MayushText> : null}</View>;
const Warning: React.FC<{ text: string; isRTL: boolean }> = ({ text, isRTL }) => <View style={[styles.warning, isRTL && styles.rowReverse]}><MayushIcon name="alert-triangle" size={25} color={colors.brand.orange500} /><MayushText variant="body" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'} style={styles.flex}>{text}</MayushText></View>;
const InfoCard: React.FC<{ icon: 'wallet' | 'info'; title: string; text: string; isRTL: boolean }> = ({ icon, title, text, isRTL }) => <OrderCard><View style={[styles.infoRow, isRTL && styles.rowReverse]}><View style={styles.metaIcon}><MayushIcon name={icon} size={22} color={colors.brand.orange500} /></View><View style={styles.flex}><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{title}</MayushText><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{text}</MayushText></View></View></OrderCard>;

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFFDF9' }, canvas: { flex: 1, width: '100%', maxWidth: 393, alignSelf: 'center' }, content: { padding: 14, paddingBottom: 30, gap: 9 },
  flex: { flex: 1 }, rowReverse: { flexDirection: 'row-reverse' }, hero: { width: 112, height: 112, borderRadius: 28, alignSelf: 'center', alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF3E7' }, heroSuccess: { backgroundColor: colors.semantic.successBackground },
  metaRow: { minHeight: 48, flexDirection: 'row', alignItems: 'center', gap: 9 }, metaIcon: { width: 40, height: 40, borderRadius: 20, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF3E7' },
  warning: { borderWidth: 1, borderColor: colors.brand.orange100, borderRadius: 11, backgroundColor: '#FFF8F0', padding: 12, flexDirection: 'row', alignItems: 'center', gap: 10 },
  reasonList: { marginTop: 10, gap: 5 }, reasonRow: { minHeight: 39, borderWidth: 1, borderColor: colors.surface.borderWarm, borderRadius: 8, paddingHorizontal: 10, flexDirection: 'row', alignItems: 'center', gap: 9 }, reasonSelected: { borderColor: colors.brand.orange500, backgroundColor: '#FFF9F3' },
  radio: { width: 18, height: 18, borderRadius: 9, borderWidth: 1, borderColor: colors.brand.navy900, alignItems: 'center', justifyContent: 'center' }, radioSelected: { borderColor: colors.brand.orange500 }, radioDot: { width: 9, height: 9, borderRadius: 5, backgroundColor: colors.brand.orange500 },
  input: { minHeight: 92, borderWidth: 1, borderColor: colors.neutral.gray300, borderRadius: 9, padding: 10, color: colors.brand.navy900, textAlignVertical: 'top', backgroundColor: colors.surface.white }, sectionTitle: { gap: 2 }, infoRow: { flexDirection: 'row', alignItems: 'flex-start', gap: 10 }, separator: { height: 1, marginVertical: 8, backgroundColor: colors.surface.borderWarm },
  totalRow: { minHeight: 38, marginTop: 8, paddingTop: 8, borderTopWidth: 1, borderTopColor: colors.surface.borderWarm, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
});
