export interface LegalDocumentSection {
  id: string;
  titleFr: string;
  titleAr: string;
  contentFr: string;
  contentAr: string;
}

export interface LegalDocument {
  id: string;
  titleFr: string;
  titleAr: string;
  lastUpdatedFr: string;
  lastUpdatedAr: string;
  sections: LegalDocumentSection[];
}

export const PRIVACY_POLICY_DOCUMENT: LegalDocument = {
  id: 'privacy-policy',
  titleFr: 'Politique de Confidentialité',
  titleAr: 'سياسة الخصوصية',
  lastUpdatedFr: 'Mis à jour en août 2026',
  lastUpdatedAr: 'تم التحديث في غشت 2026',
  sections: [
    {
      id: 'sec-1',
      titleFr: '1. Cadre Légal et Cadre de Protection (Loi 09-08)',
      titleAr: '1. الإطار القانوني وحماية البيانات (القانون 09-08)',
      contentFr:
        'Conformément à la loi marocaine n° 09-08 relative à la protection des personnes physiques à l\'égard du traitement des données à caractère personnel, Mayush Design s\'engage à protéger la vie privée de ses utilisateurs et acheteurs.',
      contentAr:
        'وفقاً للقانون المغربي رقم 09-08 المتعلق بحماية الأشخاص الذاتيين تجاه معالجة المعطيات ذات الطابع الشخصي، تلتزم مايووش ديزاين بحماية الخصوصية.',
    },
    {
      id: 'sec-2',
      titleFr: '2. Données Collectées & Finalités',
      titleAr: '2. البيانات المجمعة والأغراض',
      contentFr:
        'Nous collectons les données strictement nécessaires au traitement de vos commandes et à leur livraison à travers le Maroc : nom, prénom, numéro de téléphone, adresse de livraison et adresse e-mail.',
      contentAr:
        'نجمع البيانات الضرورية فقط لمعالجة طلباتكم وتوصيلها في المغرب: الاسم، رقم الهاتف، عنوان التوصيل والبريد الإلكتروني.',
    },
    {
      id: 'sec-3',
      titleFr: '3. Transmissions aux Tiers & Logistique',
      titleAr: '3. الشركاء والخدمات اللوجستية',
      contentFr:
        'Vos coordonnées de livraison sont transmises uniquement à nos partenaires logistiques et transporteurs agréés au Maroc pour acheminer vos commandes. Les informations de paiement sont traitées de manière chiffrée via le pont CMI.',
      contentAr:
        'تُشارك معلومات التوصيل فقط مع شركائنا في اللوجستيات بالمغرب. يتم معالجة بيانات الدفع بشكل مشفر عبر بوابة CMI.',
    },
    {
      id: 'sec-4',
      titleFr: '4. Vos Droits (Accès, Rectification, Opposition)',
      titleAr: '4. حقوقكم (الوصول، التعديل، المعارضة)',
      contentFr:
        'Vous disposez d\'un droit d\'accès, de rectification et d\'opposition concernant vos données personnelles. Pour exercer ce droit, vous pouvez contacter notre service support à contact@mayush.ma.',
      contentAr:
        'لديك الحق في الوصول إلى بياناتك الشخصية وتصحيحها أو معارضتها عن طريق التواصل مع الدعم: contact@mayush.ma.',
    },
    {
      id: 'sec-5',
      titleFr: '5. Sécurité et Conservation des Données',
      titleAr: '5. أمان وحفظ البيانات',
      contentFr:
        'Toutes les données sensibles sont stockées avec des mécanismes de chiffrement avancés. Vos données sont conservées pendant la durée légale nécessaire au traitement commercial et comptable.',
      contentAr:
        'تُحفظ جميع البيانات الحساسة باستخدام آليات تشفير متقدمة طوال الفترة القانونية المطلوبة.',
    },
  ],
};

export const TERMS_CONDITIONS_DOCUMENT: LegalDocument = {
  id: 'terms-conditions',
  titleFr: 'Conditions Générales d\'Utilisation (CGU)',
  titleAr: 'الشروط العامة للاستخدام',
  lastUpdatedFr: 'Mis à jour en août 2026',
  lastUpdatedAr: 'تم التحديث في غشت 2026',
  sections: [
    {
      id: 'cgu-1',
      titleFr: '1. Accès à la Plateforme Mayush Mobile',
      titleAr: '1. الوصول إلى منصة مايووش',
      contentFr:
        'L\'accès à l\'application Mayush Mobile est ouvert aux acheteurs résidant au Maroc. L\'utilisateur s\'engage à fournir des informations exactes lors de la création de son compte.',
      contentAr:
        'الوصول إلى تطبيق مايووش متاح للمشترين في المغرب. يلتزم المستخدم بتقديم معلومات دقيقة.',
    },
    {
      id: 'cgu-2',
      titleFr: '2. Prix et Paiement en Dirhams (MAD)',
      titleAr: '2. الأسعار والدفع بالدرهم المغربي',
      contentFr:
        'Tous les prix sont affichés en Dirhams marocains (MAD) toutes taxes comprises. Le paiement s\'effectue en ligne via CMI ou à la livraison selon les options disponibles.',
      contentAr:
        'جميع الأسعار معروضة بالدرهم المغربي شاملة للرسوم. يتم الدفع عبر CMI أو عند الاستلام.',
    },
    {
      id: 'cgu-3',
      titleFr: '3. Livraisons et Droit de Rétractation (Loi 31-08)',
      titleAr: '3. التوصيل وحق التراجع (القانون 31-08)',
      contentFr:
        'Les livraisons sont effectuées partout au Maroc. Conformément à la loi marocaine n° 31-08 édictant des mesures de protection du consommateur, vous bénéficiez d\'un délai de rétractation de 7 jours dans les conditions légales applicables.',
      contentAr:
        'يتم التوصيل لكافة المدن المغربية. وفقاً للقانون المغربي رقم 31-08 المتعلق بحماية المستهلك، يحق لكم طلب التراجع خلال 7 أيام في الحالات القابلة للتطبيق.',
    },
  ],
};
