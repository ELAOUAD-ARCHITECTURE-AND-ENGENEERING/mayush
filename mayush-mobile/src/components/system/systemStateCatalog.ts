import type { MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import type { ImageSourcePropType } from 'react-native';

export type SystemStateId =
  | 'access-denied'
  | 'account-other-device'
  | 'account-sync'
  | 'account-temporarily-blocked'
  | 'app-initialization'
  | 'app-up-to-date'
  | 'background-sync'
  | 'biometric-unavailable'
  | 'camera-access-denied'
  | 'connection-restored'
  | 'connection-timeout'
  | 'content-loading'
  | 'content-loading-skeleton'
  | 'data-restoration'
  | 'generic-error'
  | 'installation-progress'
  | 'local-data-update'
  | 'location-access-denied'
  | 'maintenance-completed'
  | 'notifications-disabled'
  | 'offline'
  | 'offline-cached-content'
  | 'page-not-found'
  | 'password-changed'
  | 'photos-access-denied'
  | 'reconnection-progress'
  | 'refresh-progress'
  | 'scheduled-maintenance'
  | 'server-error-500'
  | 'server-unavailable'
  | 'session-expired'
  | 'session-restoration'
  | 'session-restored'
  | 'slow-connection'
  | 'too-many-attempts'
  | 'unusual-activity-verification'
  | 'update-available'
  | 'update-download-progress'
  | 'update-failed'
  | 'update-required';

type LocalizedText = { fr: string; ar: string };

export interface SystemStateDefinition {
  icon: MayushIconName;
  illustrationSource?: ImageSourcePropType;
  illustrationMode?: 'contain' | 'reference-crop';
  illustrationFrameHeight?: number;
  illustrationCropTop?: number;
  illustrationCropHeight?: number;
  illustrationMaxWidth?: number;
  illustrationMarginTop?: number;
  illustrationMarginBottom?: number;
  compactSpacing?: boolean;
  statusItemIcons?: MayushIconName[];
  accentColor?: string;
  backgroundColor?: string;
  titleBeforeIllustration?: boolean;
  titleSans?: boolean;
  primaryDisabled?: boolean;
  title: LocalizedText;
  message: LocalizedText;
  detailTitle?: LocalizedText;
  detailMessage?: LocalizedText;
  detailIcon?: MayushIconName;
  detailIconColor?: string;
  detailIconBackgroundColor?: string;
  showConnectingDots?: boolean;
  primaryLabel?: LocalizedText;
  secondaryLabel?: LocalizedText;
  secondaryIcon?: MayushIconName;
  secondaryVariant?: 'orange' | 'navy';
  progress?: number;
  progressLabel?: LocalizedText;
  statusItems?: LocalizedText[];
  statusItemsComplete?: boolean;
}

const text = (fr: string, ar: string): LocalizedText => ({ fr, ar });

const referenceArtwork: Partial<Record<SystemStateId, { fr?: ImageSourcePropType; ar?: ImageSourcePropType }>> = {
  'access-denied': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-access-denied-403-fr.png') },
  'account-other-device': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-account-other-device-fr.png') },
  'account-sync': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-account-sync-fr.png') },
  'account-temporarily-blocked': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-account-temporarily-blocked-fr.png') },
  'app-initialization': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-app-initialization-fr.png') },
  'app-up-to-date': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-app-up-to-date-fr.png') },
  'background-sync': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-background-sync-fr.png') },
  'biometric-unavailable': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-biometric-unavailable-fr.png') },
  'camera-access-denied': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-camera-access-denied-fr.png') },
  'connection-restored': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-connection-restored-fr.png') },
  'connection-timeout': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-connection-timeout-fr.png') },
  'data-restoration': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-data-restoration-progress-fr.png') },
  'generic-error': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-generic-error-fr.png') },
  'installation-progress': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-installation-progress-fr.png') },
  'local-data-update': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-local-data-update-progress-fr.png') },
  'location-access-denied': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-location-access-denied-fr.png') },
  'maintenance-completed': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-maintenance-completed-fr.png'), ar: require('../../../design-reference/mayush-mobile-design/10-system-states/10-maintenance-completed-ar.png') },
  'notifications-disabled': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-notifications-disabled-fr.png') },
  offline: { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-offline-fr.png'), ar: require('../../../design-reference/mayush-mobile-design/10-system-states/10-offline-ar.png') },
  'offline-cached-content': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-offline-cached-content-fr.png') },
  'page-not-found': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-page-not-found-fr.png') },
  'password-changed': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-password-changed-fr.png') },
  'photos-access-denied': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-photos-access-denied-fr.png') },
  'reconnection-progress': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-reconnection-progress-fr.png') },
  'refresh-progress': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-refresh-progress-fr.png') },
  'scheduled-maintenance': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-scheduled-maintenance-fr.png') },
  'server-error-500': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-server-error-500-fr.png') },
  'server-unavailable': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-server-unavailable-detailed-fr.png'), ar: require('../../../design-reference/mayush-mobile-design/10-system-states/10-server-unavailable-ar.png') },
  'session-expired': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-session-expired-fr.png'), ar: require('../../../design-reference/mayush-mobile-design/10-system-states/10-session-expired-ar.png') },
  'session-restoration': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-session-restoration-fr.png') },
  'session-restored': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-session-restored-fr.png') },
  'slow-connection': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-slow-connection-fr.png') },
  'too-many-attempts': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-too-many-attempts-fr.png') },
  'unusual-activity-verification': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-unusual-activity-verification-fr.png') },
  'update-available': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-update-available-fr.png') },
  'update-download-progress': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-update-download-progress-fr.png') },
  'update-failed': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-update-failed-fr.png') },
  'update-required': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-update-required-fr.png'), ar: require('../../../design-reference/mayush-mobile-design/10-system-states/10-update-required-ar.png') },
  'content-loading': {},
  'content-loading-skeleton': { fr: require('../../../design-reference/mayush-mobile-design/10-system-states/10-content-loading-skeleton-fr.png') },
};

export const systemStateCatalog: Record<SystemStateId, SystemStateDefinition> = {
  'access-denied': { icon: 'shield', title: text('Accès refusé', 'تم رفض الوصول'), message: text("Vous n’êtes pas autorisé à accéder à cette page.", 'ليس لديك إذن للوصول إلى هذه الصفحة.'), detailMessage: text('Certaines sections sont réservées selon votre accès.', 'بعض الأقسام مخصصة حسب مستوى الوصول.'), primaryLabel: text('Retour', 'رجوع'), secondaryLabel: text('Compte', 'الحساب') },
  'account-other-device': { icon: 'shield-check', title: text('Compte utilisé sur un autre appareil', 'الحساب مستخدم على جهاز آخر'), message: text('Pour votre sécurité, veuillez confirmer que c’est bien vous.', 'لحماية حسابك، يرجى تأكيد هويتك.'), titleBeforeIllustration: true, titleSans: true, primaryLabel: text('Vérifier maintenant', 'تحقق الآن'), secondaryLabel: text('Se déconnecter', 'تسجيل الخروج') },
  'account-sync': { icon: 'refresh-cw', title: text('Synchronisation de votre compte', 'مزامنة حسابك'), message: text('Nous mettons à jour vos commandes, favoris et préférences.', 'نقوم بتحديث طلباتك ومفضلاتك وتفضيلاتك.'), statusItems: [text('Commandes', 'الطلبات'), text('Favoris', 'المفضلة'), text('Préférences', 'التفضيلات')] },
  'account-temporarily-blocked': { icon: 'lock', title: text('Compte temporairement bloqué', 'الحساب محظور مؤقتًا'), message: text('Veuillez patienter avant de réessayer.', 'انتظر قبل إعادة المحاولة.'), titleBeforeIllustration: true, titleSans: true, detailTitle: text('Déblocage dans', 'إلغاء الحظر بعد'), detailMessage: text('15:00', '15:00'), primaryLabel: text('Contacter le support', 'الاتصال بالدعم'), secondaryLabel: text('Fermer', 'إغلاق') },
  'app-initialization': { icon: 'refresh-cw', title: text('Initialisation de l’application', 'تهيئة التطبيق'), message: text('Nous préparons votre expérience Mayush.', 'نقوم بإعداد تجربة مايوش.'), progressLabel: text('Initialisation', 'التهيئة') },
  'app-up-to-date': { icon: 'check-circle', title: text('Application à jour', 'التطبيق محدّث'), message: text('Vous utilisez la dernière version disponible de Mayush.', 'أنت تستخدم أحدث إصدار متاح من مايوش.'), detailTitle: text('Version actuelle · Dernière vérification', 'الإصدار الحالي · آخر تحقق'), detailMessage: text('1.0.0 · À l’instant', '1.0.0 · الآن'), primaryLabel: text('Continuer', 'متابعة') },
  'background-sync': { 
    icon: 'refresh-cw', 
    illustrationSource: require('../../../assets/reference-art/background-sync-art.png'), 
    illustrationMaxWidth: 240, 
    illustrationMarginTop: 2, 
    illustrationMarginBottom: 0, 
    compactSpacing: true, 
    title: text('Synchronisation en arrière-plan', 'المزامنة في الخلفية'), 
    message: text('Vous pouvez continuer à utiliser l’application pendant la mise à jour.', 'يمكنك متابعة استخدام التطبيق أثناء التحديث.'), 
    statusItems: [text('Commandes', 'الطلبات'), text('Demandes d’annulation', 'طلبات الإلغاء'), text('Remboursements', 'الاستردادات')], 
    statusItemIcons: ['box', 'file-text', 'credit-card'],
    accentColor: '#F97316',
    progressLabel: text('Synchronisation des données', 'مزامنة البيانات') 
  },
  'biometric-unavailable': { icon: 'lock', title: text('Authentification biométrique indisponible', 'المصادقة البيومترية غير متاحة'), message: text('Utilisez votre mot de passe pour continuer.', 'استخدم كلمة المرور للمتابعة.'), titleBeforeIllustration: true, titleSans: true, detailMessage: text('Vos données restent sécurisées en toutes circonstances.', 'تبقى بياناتك آمنة في جميع الظروف.'), primaryLabel: text('Continuer avec le mot de passe', 'المتابعة بكلمة المرور'), secondaryLabel: text('Plus tard', 'لاحقًا') },
  'camera-access-denied': { icon: 'camera', title: text('Accès à la caméra refusé', 'تم رفض الوصول إلى الكاميرا'), message: text('Autorisez la caméra dans les réglages pour utiliser cette fonctionnalité.', 'اسمح بالوصول إلى الكاميرا من الإعدادات لاستخدام هذه الميزة.'), primaryLabel: text('Ouvrir les réglages', 'فتح الإعدادات'), secondaryLabel: text('Plus tard', 'لاحقًا') },
  'connection-restored': { icon: 'check-circle', title: text('Connexion rétablie', 'تمت استعادة الاتصال'), message: text('Vous êtes de nouveau connecté à Internet.', 'أنت متصل بالإنترنت من جديد.'), detailMessage: text('Synchronisation reprise', 'تم استئناف المزامنة'), primaryLabel: text('Continuer', 'متابعة'), secondaryLabel: text('Voir les nouveautés', 'عرض المستجدات') },
  'connection-timeout': { icon: 'clock', illustrationSource: require('../../../assets/reference-art/system-connection-timeout.png'), title: text('Délai de connexion expiré', 'انتهت مهلة الاتصال'), message: text('Le serveur a mis trop de temps à répondre.', 'استغرق الخادم وقتًا طويلًا للرد.'), detailTitle: text('Votre demande n’a pas abouti.', 'لم يكتمل طلبك.'), detailMessage: text('Veuillez réessayer.', 'يرجى إعادة المحاولة.'), primaryLabel: text('Réessayer', 'إعادة المحاولة'), secondaryLabel: text('Fermer', 'إغلاق') },
  'content-loading': { icon: 'refresh-cw', illustrationSource: require('../../../assets/reference-art/content-loading-illustration.png'), title: text('Chargement du contenu', 'جارٍ تحميل المحتوى'), message: text('Veuillez patienter pendant le chargement.', 'يرجى الانتظار أثناء تجهيز تجربتك.'), titleBeforeIllustration: true, progressLabel: text('Chargement', 'التحميل'), secondaryLabel: text('Annuler', 'إلغاء') },
  'content-loading-skeleton': { icon: 'refresh-cw', title: text('Chargement du contenu', 'جاري تحميل المحتوى'), message: text('Veuillez patienter quelques instants.', 'يرجى الانتظار لبضع لحظات.'), titleBeforeIllustration: true, illustrationFrameHeight: 430, illustrationCropHeight: 420, illustrationCropTop: -100 },
  'data-restoration': { icon: 'download', illustrationSource: require('../../../assets/reference-art/data-restoration-cloud-art.png'), illustrationMaxWidth: 300, illustrationMarginTop: 4, illustrationMarginBottom: -5, compactSpacing: true, title: text('Restauration de vos données', 'استعادة بياناتك'), message: text('Votre environnement personnel est en cours de récupération.', 'تتم استعادة بيئتك الشخصية.'), titleBeforeIllustration: true, statusItems: [text('Favoris', 'المفضلة'), text('Adresses', 'العناوين'), text('Historique', 'السجل')], statusItemIcons: ['star', 'map-pin', 'clock'], accentColor: '#2563EB', progressLabel: text('Restauration en cours…', 'الاستعادة جارية…') },
  'generic-error': {
    icon: 'alert-triangle',
    illustrationSource: require('../../../assets/reference-art/generic-error-art.png'),
    illustrationMaxWidth: 260,
    illustrationMarginTop: 4,
    illustrationMarginBottom: 0,
    compactSpacing: true,
    title: text('Une erreur est survenue', 'حدث خطأ'),
    message: text('Nous rencontrons un problème technique.', 'نواجه مشكلة فنية حاليًا.'),
    detailIcon: 'info',
    detailIconColor: '#1F2A3A',
    detailIconBackgroundColor: '#F1F5F9',
    detailMessage: text('Le problème a été signalé automatiquement.', 'تم الإبلاغ عن المشكلة تلقائيًا.'),
    primaryLabel: text('Réessayer', 'إعادة المحاولة'),
    secondaryIcon: 'home',
    secondaryVariant: 'navy',
    secondaryLabel: text('Retour à l’accueil', 'العودة إلى الرئيسية'),
  },
  'installation-progress': { icon: 'download', title: text('Installation en cours', 'جارٍ التثبيت'), message: text('N’éteignez pas votre appareil.', 'لا تقم بإيقاف تشغيل جهازك.'), titleBeforeIllustration: true, titleSans: true, detailTitle: text('Ne fermez pas l’application', 'لا تغلق التطبيق'), detailMessage: text('La fermeture de l’application peut interrompre l’installation et causer des erreurs.', 'قد يؤدي إغلاق التطبيق إلى مقاطعة التثبيت.'), progressLabel: text('Installation', 'التثبيت') },
  'local-data-update': { icon: 'refresh-cw', title: text('Actualisation des données locales', 'تحديث البيانات المحلية'), message: text('Nous nettoyons temporairement les données mises en cache.', 'نقوم بتنظيف البيانات المخزنة مؤقتًا.'), titleBeforeIllustration: true, progressLabel: text('Actualisation', 'التحديث'), secondaryLabel: text('Annuler', 'إلغاء') },
  'location-access-denied': { icon: 'map-pin', title: text('Accès à la localisation refusé', 'تم رفض الوصول إلى الموقع'), message: text('Autorisez votre position pour des estimations de livraison plus précises.', 'اسمح بالوصول إلى موقعك لتقديرات توصيل أكثر دقة.'), titleSans: true, primaryLabel: text('Ouvrir les paramètres', 'فتح الإعدادات'), secondaryLabel: text('Plus tard', 'لاحقًا') },
  'maintenance-completed': { icon: 'check-circle', title: text('Maintenance terminée', 'اكتملت الصيانة'), message: text('Tous les services Mayush sont de nouveau disponibles.', 'جميع خدمات مايوش متاحة من جديد.'), primaryLabel: text('Continuer', 'متابعة'), secondaryLabel: text('Voir les nouveautés', 'عرض المستجدات') },
  'notifications-disabled': { icon: 'bell', title: text('Notifications désactivées', 'الإشعارات معطلة'), message: text('Activez les notifications dans les réglages pour ne rien manquer.', 'فعّل الإشعارات من الإعدادات حتى لا يفوتك شيء.'), detailMessage: text('Commandes, livraisons et offres importantes.', 'الطلبات والتوصيل والعروض المهمة.'), primaryLabel: text('Ouvrir les réglages', 'فتح الإعدادات'), secondaryLabel: text('Plus tard', 'لاحقًا') },
  offline: { icon: 'wifi-off', title: text('Vous êtes hors ligne', 'أنت غير متصل بالإنترنت'), message: text('Vérifiez votre connexion Internet puis réessayez.', 'تحقق من اتصالك بالإنترنت ثم أعد المحاولة.'), primaryLabel: text('Réessayer', 'إعادة المحاولة'), secondaryLabel: text('Fermer', 'إغلاق') },
  'offline-cached-content': { icon: 'wifi-off', title: text('Mode hors ligne', 'وضع عدم الاتصال'), message: text('Seules les données enregistrées et vérifiées restent disponibles.', 'تتوفر فقط البيانات المحفوظة والتي تم التحقق منها.'), detailTitle: text('Contenu disponible', 'المحتوى المتاح'), statusItems: [text('Commandes récentes', 'الطلبات الأخيرة'), text('Favoris enregistrés', 'المفضلة المحفوظة'), text('Panier local', 'السلة المحلية')], statusItemsComplete: true, primaryLabel: text('Réessayer la connexion', 'إعادة محاولة الاتصال') },
  'page-not-found': { icon: 'alert-triangle', title: text('Page introuvable', 'الصفحة غير موجودة'), message: text('La page demandée est introuvable ou a été déplacée.', 'الصفحة المطلوبة غير موجودة أو تم نقلها.'), primaryLabel: text('Retour', 'رجوع'), secondaryLabel: text('Accueil', 'الرئيسية') },
  'password-changed': { icon: 'shield-check', title: text('Mot de passe modifié', 'تم تغيير كلمة المرور'), message: text('Votre mot de passe a été modifié avec succès.', 'تم تغيير كلمة المرور بنجاح.'), statusItems: [text('Compte sécurisé', 'الحساب مؤمّن'), text('Sessions déconnectées', 'تم إنهاء الجلسات')], statusItemsComplete: true, detailMessage: text('Reconnectez-vous avec votre nouveau mot de passe.', 'سجّل الدخول بكلمة المرور الجديدة.'), primaryLabel: text('Fermer', 'إغلاق') },
  'photos-access-denied': { icon: 'image', title: text('Accès aux photos refusé', 'تم رفض الوصول إلى الصور'), message: text('Autorisez l’accès aux photos dans les réglages pour continuer.', 'اسمح بالوصول إلى الصور من الإعدادات للمتابعة.'), primaryLabel: text('Ouvrir les réglages', 'فتح الإعدادات'), secondaryLabel: text('Plus tard', 'لاحقًا') },
  'reconnection-progress': { 
    icon: 'refresh-cw', 
    illustrationSource: require('../../../assets/reference-art/reconnection-progress-art.png'), 
    illustrationMaxWidth: 250, 
    illustrationMarginTop: 4, 
    illustrationMarginBottom: 0, 
    compactSpacing: true, 
    titleSans: true, 
    title: text('Reconnexion en cours', 'جارٍ إعادة الاتصال'), 
    message: text('Tentative de reconnexion au serveur...', 'محاولة إعادة الاتصال بالخادم...'), 
    detailIcon: 'refresh-cw', 
    detailIconColor: '#F97316', 
    detailMessage: text('Connexion en cours', 'جارٍ الاتصال'), 
    showConnectingDots: true, 
    secondaryLabel: text('Annuler', 'إلغاء') 
  },
  'refresh-progress': { icon: 'refresh-cw', title: text('Actualisation en cours', 'جارٍ التحديث'), message: text('Le contenu est en cours d’actualisation.', 'يتم تحديث المحتوى.'), progressLabel: text('Actualisation', 'التحديث'), secondaryLabel: text('Annuler', 'إلغاء') },
  'scheduled-maintenance': { icon: 'wrench', title: text('Maintenance programmée', 'صيانة مجدولة'), message: text('Une maintenance est prévue prochainement.', 'تمت جدولة صيانة قريبًا.'), detailTitle: text('Disponibilité estimée', 'الوقت المتوقع للتوفر'), detailMessage: text('Les horaires sont communiqués par le service Mayush.', 'يتم توفير المواعيد من خدمة مايوش.'), primaryLabel: text('J’ai compris', 'فهمت'), secondaryLabel: text('État des services', 'حالة الخدمات') },
  'server-error-500': { icon: 'alert-triangle', title: text('Erreur interne du serveur', 'خطأ داخلي في الخادم'), message: text('Le serveur a rencontré une erreur inattendue.', 'واجه الخادم خطأ غير متوقع.'), primaryLabel: text('Réessayer', 'إعادة المحاولة'), secondaryLabel: text('Fermer', 'إغلاق') },
  'server-unavailable': { icon: 'alert-triangle', title: text('Serveur indisponible', 'الخادم غير متاح'), message: text('Les services Mayush sont temporairement indisponibles.', 'خدمات مايوش غير متاحة مؤقتًا.'), primaryLabel: text('Réessayer', 'إعادة المحاولة'), secondaryLabel: text('Contacter le support', 'الاتصال بالدعم') },
  'session-expired': { 
    icon: 'lock', 
    illustrationSource: require('../../../assets/reference-art/session-expired-art.png'), 
    illustrationMaxWidth: 250, 
    illustrationMarginTop: 4, 
    illustrationMarginBottom: 0, 
    compactSpacing: true, 
    titleSans: true, 
    title: text('Session expirée', 'انتهت صلاحية الجلسة'), 
    message: text('Veuillez vous reconnecter pour continuer.', 'يرجى تسجيل الدخول مجددًا للمتابعة.'), 
    detailMessage: text('Votre session a expiré pour des raisons de sécurité.', 'انتهت صلاحية جلستك لأسباب أمنية.'), 
    primaryLabel: text('Se reconnecter', 'تسجيل الدخول'), 
    secondaryLabel: text('Retour à l’accueil', 'العودة إلى الرئيسية') 
  },
  'session-restoration': { icon: 'refresh-cw', title: text('Restauration de votre session', 'استعادة جلستك'), message: text('Nous vérifions et restaurons votre session sécurisée.', 'نتحقق من جلستك الآمنة ونستعيدها.'), progressLabel: text('Restauration', 'الاستعادة') },
  'session-restored': { icon: 'shield-check', illustrationSource: require('../../../assets/reference-art/session-restored-shield-art.png'), illustrationMaxWidth: 240, illustrationMarginTop: 4, illustrationMarginBottom: 0, compactSpacing: true, titleSans: true, title: text('Session restaurée en toute sécurité', 'تمت استعادة الجلسة بأمان'), message: text('Votre session a été récupérée avec succès.', 'تمت استعادة جلستك بنجاح.'), detailTitle: text('Dernière activité vérifiée', 'آخر نشاط تم التحقق منه'), detailMessage: text('Session active et sécurisée', 'جلسة نشطة وآمنة'), primaryLabel: text('Continuer', 'متابعة'), secondaryLabel: text('Voir l’activité', 'عرض النشاط') },
  'slow-connection': { icon: 'wifi-off', title: text('Connexion lente', 'اتصال بطيء'), message: text('Le chargement prend plus de temps que prévu.', 'يستغرق التحميل وقتًا أطول من المتوقع.'), primaryLabel: text('Réessayer', 'إعادة المحاولة'), secondaryLabel: text('Patienter', 'انتظار') },
  'too-many-attempts': { icon: 'clock', title: text('Trop de tentatives', 'محاولات كثيرة جدًا'), message: text('Patientez avant de réessayer.', 'انتظر قبل إعادة المحاولة.'), detailTitle: text('Nouvelle tentative dans', 'المحاولة التالية بعد'), detailMessage: text('00:30', '00:30'), primaryLabel: text('Veuillez patienter', 'يرجى الانتظار'), primaryDisabled: true, secondaryLabel: text('Fermer', 'إغلاق') },
  'unusual-activity-verification': { icon: 'shield-check', title: text('Activité inhabituelle détectée', 'تم اكتشاف نشاط غير معتاد'), message: text('Confirmez votre identité pour sécuriser votre compte.', 'أكد هويتك لتأمين حسابك.'), primaryLabel: text('Vérifier maintenant', 'تحقق الآن'), secondaryLabel: text('Fermer', 'إغلاق') },
  'update-available': { icon: 'download', title: text('Une mise à jour est disponible', 'يتوفر تحديث جديد'), message: text('Mettez Mayush à jour pour profiter des dernières améliorations.', 'حدّث مايوش للاستفادة من أحدث التحسينات.'), detailTitle: text('Nouvelle version', 'الإصدار الجديد'), primaryLabel: text('Mettre à jour', 'تحديث'), secondaryLabel: text('Plus tard', 'لاحقًا') },
  'update-download-progress': { icon: 'download', title: text('Téléchargement de la mise à jour', 'جارٍ تنزيل التحديث'), message: text('La mise à jour compatible est en cours de téléchargement.', 'يتم تنزيل التحديث المتوافق.'), progressLabel: text('Téléchargement', 'التنزيل'), secondaryLabel: text('Annuler', 'إلغاء') },
  'update-failed': { icon: 'alert-triangle', title: text('Échec de la mise à jour', 'فشل التحديث'), message: text('La mise à jour n’a pas pu être téléchargée ou appliquée.', 'تعذر تنزيل التحديث أو تطبيقه.'), primaryLabel: text('Réessayer', 'إعادة المحاولة'), secondaryLabel: text('Plus tard', 'لاحقًا') },
  'update-required': { icon: 'shield-check', title: text('Mise à jour requise', 'التحديث مطلوب'), message: text('Installez la version requise pour continuer à utiliser Mayush.', 'ثبّت الإصدار المطلوب لمواصلة استخدام مايوش.'), detailTitle: text('Version minimale requise', 'الحد الأدنى للإصدار المطلوب'), primaryLabel: text('Mettre à jour maintenant', 'تحديث الآن'), secondaryLabel: text('Fermer', 'إغلاق') },
};

export const getSystemStateDefinition = (stateId: SystemStateId, language: 'fr' | 'ar') => {
  const definition = systemStateCatalog[stateId];
  const artwork = referenceArtwork[stateId];
  const localize = (value?: LocalizedText) => value?.[language];
  return {
    ...definition,
    illustrationSource: definition.illustrationSource || (artwork ? (language === 'ar' ? artwork.ar || artwork.fr : artwork.fr) : undefined),
    illustrationMode: definition.illustrationSource ? definition.illustrationMode || 'contain' : 'reference-crop',
    illustrationCropHeight: definition.illustrationCropHeight,
    illustrationMaxWidth: definition.illustrationMaxWidth,
    illustrationMarginTop: definition.illustrationMarginTop,
    illustrationMarginBottom: definition.illustrationMarginBottom,
    detailIcon: definition.detailIcon,
    detailIconColor: definition.detailIconColor,
    detailIconBackgroundColor: definition.detailIconBackgroundColor,
    showConnectingDots: definition.showConnectingDots,
    statusItemIcons: definition.statusItemIcons,
    accentColor: definition.accentColor,
    backgroundColor: definition.backgroundColor,
    title: localize(definition.title) as string,
    message: localize(definition.message) as string,
    detailTitle: localize(definition.detailTitle),
    detailMessage: localize(definition.detailMessage),
    primaryLabel: localize(definition.primaryLabel),
    secondaryLabel: localize(definition.secondaryLabel),
    secondaryIcon: definition.secondaryIcon,
    secondaryVariant: definition.secondaryVariant,
    progressLabel: localize(definition.progressLabel),
    statusItems: definition.statusItems?.map((item) => item[language]),
  };
};
