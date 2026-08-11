import AsyncStorage from '@react-native-async-storage/async-storage';
import { BuyerOrderRepository, orderState } from './orderState';

// ── FAQ Types ──

export interface FaqCategory {
  id: string;
  label: string;
  labelAr: string;
  icon: string;
  subtitleFr?: string;
  subtitleAr?: string;
}

export interface FaqStep {
  stepNumber: number;
  title: string;
  titleAr: string;
  description: string;
  descriptionAr: string;
}

export interface FaqItem {
  id: string;
  categoryId: string;
  question: string;
  questionAr: string;
  answer: string;
  answerAr: string;
  subtitle?: string;
  subtitleAr?: string;
  steps?: FaqStep[];
}

// ── Support Request & Ticket Types ──

export interface SupportAttachment {
  id: string;
  name: string;
  size: string;
  type: 'image' | 'document';
  uri?: string;
}

export interface SupportMessage {
  id: string;
  sender: 'user' | 'agent';
  senderName: string;
  senderAvatar?: string;
  timestamp: string;
  text: string;
  statusBadge?: string;
  attachments?: SupportAttachment[];
}

export interface SupportRequest {
  id: string;
  reference: string;
  title: string;
  titleAr: string;
  categoryId: string;
  categoryLabel?: string;
  status: 'open' | 'in-progress' | 'resolved' | 'closed';
  statusLabel: string;
  statusLabelAr: string;
  date: string;
  summary: string;
  summaryAr: string;
  relatedOrderId?: string;
  unreadCount?: number;
  priority?: 'Normale' | 'Haute' | 'Urgente';
  messages: SupportMessage[];
  attachments?: SupportAttachment[];
  userEmail?: string;
  preferredChannel?: 'Email' | 'WhatsApp';
  rating?: {
    stars: number;
    comment?: string;
    ratedAt: string;
  };
}

export interface ContactDraft {
  subject: string;
  categoryId: string;
  message: string;
  email: string;
  preferredChannel: 'Email' | 'WhatsApp';
  selectedOrderId?: string;
  returnRequestId?: string;
  refundId?: string;
  attachments: SupportAttachment[];
}

export interface ReplyDraft {
  ticketId: string;
  message: string;
  attachments: SupportAttachment[];
}

// ── Contact Channel Types ──

export interface ContactChannel {
  id: string;
  type: 'phone' | 'email' | 'chat';
  label: string;
  labelAr: string;
  value: string;
  icon: string;
}

export interface SearchResult {
  articles: FaqItem[];
  categories: FaqCategory[];
  totalResults: number;
}

// ── Storage Key ──

const SUPPORT_STORAGE_KEY = 'mayush-mobile:support-state';

// ── Deterministic Fixtures ──

const FAQ_CATEGORIES: FaqCategory[] = [
  { id: 'commandes', label: 'Commandes & Livraison', labelAr: 'الطلبات والتوصيل', icon: 'package', subtitleFr: 'Suivi, délais et informations de livraison', subtitleAr: 'المتابعة، المواعيد ومعلومات التوصيل' },
  { id: 'paiement', label: 'Paiement & Facturation', labelAr: 'الدفع والفوترة', icon: 'credit-card', subtitleFr: 'Méthodes de paiement et factures', subtitleAr: 'طرق الدفع والفواتير' },
  { id: 'compte', label: 'Mon Compte & Sécurité', labelAr: 'حسابي والأمان', icon: 'user', subtitleFr: 'Gestion de compte et sécurité', subtitleAr: 'إدارة الحساب والأمان' },
  { id: 'retours', label: 'Retours & Remboursements', labelAr: 'الإرجاع والاسترداد', icon: 'rotate-ccw', subtitleFr: 'Annulations, retours et remboursements', subtitleAr: 'الإلغاء، الإرجاع والاسترداد' },
  { id: 'produits', label: 'Produits & Disponibilité', labelAr: 'المنتجات والتوفر', icon: 'box', subtitleFr: 'Garanties et stock des produits', subtitleAr: 'الضمانات وتوفر المنتجات' },
  { id: 'securite', label: 'Sécurité & Données', labelAr: 'الأمان والبيانات', icon: 'shield', subtitleFr: 'Protections des données personnelles et sécurité', subtitleAr: 'حماية البيانات الشخصية والأمان' },
];

const FAQ_ITEMS: FaqItem[] = [
  {
    id: 'faq-1',
    categoryId: 'commandes',
    question: 'Comment suivre ma commande ?',
    questionAr: 'كيف أتتبع طلبي؟',
    subtitle: "Suivez l'état de votre commande à chaque étape jusqu'à la livraison.",
    subtitleAr: 'تابع حالة طلبك في كل خطوة حتى التسليم.',
    answer: 'Rendez-vous dans « Mes Commandes » depuis votre tableau de bord. Chaque commande affiche son statut en temps réel : En préparation, Expédiée, En livraison ou Livrée. Vous recevrez également des notifications Push et SMS à chaque étape.',
    answerAr: 'اذهب إلى «طلباتي» من لوحة التحكم الخاصة بك. يعرض كل طلب حالته في الوقت الفعلي: قيد التحضير، تم الشحن، في التوصيل أو تم التسليم.',
    steps: [
      {
        stepNumber: 1,
        title: 'Accédez à vos commandes',
        titleAr: 'الوصول إلى طلباتك',
        description: 'Rendez-vous dans la section « Mes commandes » depuis votre compte.',
        descriptionAr: 'انتقل إلى قسم «طلباتي» من حسابك.',
      },
      {
        stepNumber: 2,
        title: 'Sélectionnez la commande concernée',
        titleAr: 'حدد الطلب المعني',
        description: 'Choisissez la commande que vous souhaitez suivre dans la liste.',
        descriptionAr: 'اختر الطلب الذي تريد تتبعه من القائمة.',
      },
      {
        stepNumber: 3,
        title: 'Consultez le statut d’expédition',
        titleAr: 'الاطلاع على حالة الشحن',
        description: 'Vérifiez l’avancement et la date estimée de livraison.',
        descriptionAr: 'تحقق من تقدم الشحن والتاريخ المتوقع للتسليم.',
      },
      {
        stepNumber: 4,
        title: 'Cliquez sur le numéro de suivi CTM / Transporteur',
        titleAr: 'انقر على رقم تتبع CTM / الناقل',
        description: 'Pour voir la géolocalisation en temps réel de votre colis.',
        descriptionAr: 'لرؤية تحديد الموقع الجغرافي لطرودك في الوقت الفعلي.',
      },
    ],
  },
  {
    id: 'faq-2',
    categoryId: 'commandes',
    question: 'Quels sont les délais de livraison au Maroc ?',
    questionAr: 'ما هي مواعيد التوصيل في المغرب؟',
    answer: 'Les délais varient selon votre ville : 24h à 48h pour Casablanca, Rabat, Marrakech et Tanger. Pour les autres régions, comptez 3 à 5 jours ouvrés. La livraison sur rendez-vous est disponible pour le mobilier lourd.',
    answerAr: 'تختلف المواعيد حسب مدينتك: 24 إلى 48 ساعة للدار البيضاء، الرباط، مراكش وتنجة. بالنسبة للمناطق الأخرى، من 3 إلى 5 أيام عمل.',
  },
  {
    id: 'faq-3',
    categoryId: 'paiement',
    question: 'Quels modes de paiement sont acceptés ?',
    questionAr: 'ما هي طرق الدفع المقبولة؟',
    answer: 'Mayush accepte le paiement par carte bancaire marocaine et internationale via CMI sécurisé, le paiement à la livraison (Cash on Delivery) et le virement bancaire pour les commandes de mobilier volumineux.',
    answerAr: 'يقبل مايوش الدفع بالبطاقة البنكية المغربية والدولية عبر CMI الآمن، والدفع عند الاستلام، والتحويل البنكي.',
  },
  {
    id: 'faq-4',
    categoryId: 'paiement',
    question: 'Le paiement à la livraison est-il disponible pour tous les produits ?',
    questionAr: 'هل الدفع عند الاستلام متاح لجميع المنتجات؟',
    answer: 'Le paiement à la livraison est disponible pour toute commande inférieure à 10 000 MAD. Au-delà, un acompte de 30% par carte ou virement est requis lors de la confirmation.',
    answerAr: 'الدفع عند الاستلام متاح لجميع الطلبات التي تقل عن 10000 درهم مغربي.',
  },
  {
    id: 'faq-5',
    categoryId: 'retours',
    question: 'Quelle est la politique de retour et de rétractation ?',
    questionAr: 'ما هي سياسة الإرجاع والتراجع؟',
    answer: 'Conformément à la Loi 31-08 édictant des mesures de protection du consommateur au Maroc, vous disposez d\'un délai de rétractation de 7 jours dans les cas applicables pour retourner un article non utilisé dans son emballage d\'origine.',
    answerAr: 'وفقاً للقانون 31-08 المتعلق بحماية المستهلك في المغرب، يحق لكم طلب التراجع خلال 7 أيام في الحالات القابلة للتطبيق للإرجاع.',
  },
  {
    id: 'faq-6',
    categoryId: 'compte',
    question: 'Comment modifier mes informations personnelles ?',
    questionAr: 'كيف أعدل معلوماتي الشخصية؟',
    answer: 'Connectez-vous à votre compte, accédez à « Profil & Sécurité » depuis le menu Compte pour modifier votre nom, numéro de téléphone, adresse email ou mot de passe.',
    answerAr: 'سجل الدخول إلى حسابك، وانتقل إلى «الملف الشخصي والأمان» لتعديل معلوماتك.',
  },
  {
    id: 'faq-7',
    categoryId: 'produits',
    question: 'Les produits bénéficient-ils d’une garantie ?',
    questionAr: 'هل تتمتع المنتجات بضمان؟',
    answer: 'Oui, tous les meubles et luminaires vendus sur Mayush bénéficient d’une garantie fabricant de 2 ans minimum. Les détails de la garantie figurent sur la fiche produit.',
    answerAr: 'نعم، جميع الأثاث والإضاءة المباعة على مايوش تتمتع بضمان مصنع لا يقل عن سنتين.',
  },
  {
    id: 'faq-8',
    categoryId: 'commandes',
    question: 'Puis-je modifier ou annuler ma commande ?',
    questionAr: 'هل يمكنني تعديل أو إلغاء طلبي؟',
    answer: 'Vous pouvez annuler ou modifier votre commande tant qu’elle est en statut « En préparation ». Contactez le support via l’onglet « Contacter le support ».',
    answerAr: 'يمكنك إلغاء أو تعديل طلبك طالما أن حالته «قيد التحضير».',
  },
  {
    id: 'faq-9',
    categoryId: 'securite',
    question: 'Comment mes données personnelles sont-elles protégées ?',
    questionAr: 'كيف يتم حماية بياناتي الشخصية؟',
    answer: 'Vos données sont traitées en stricte conformité avec la Loi marocaine n° 09-08 relative à la protection des personnes physiques à l’égard du traitement des données à caractère personnel.',
    answerAr: 'تتم معالجة بياناتك وفقاً للقانون المغربي رقم 09-08 المتعلق بحماية الأشخاص الذاتيين تجاه معالجة المعطيات ذات الطابع الشخصي.',
  },
];

const SEEDED_SUPPORT_REQUESTS: SupportRequest[] = [
  {
    id: 'req-1',
    reference: 'SUP-2026-001842',
    title: 'Retard de livraison de ma commande',
    titleAr: 'تأخير في توصيل طلبي',
    categoryId: 'commandes',
    categoryLabel: 'Commandes & Livraison',
    status: 'open',
    statusLabel: 'Ouvert',
    statusLabelAr: 'مفتوح',
    date: '28 mai 2026 à 10:24',
    summary: 'Bonjour, ma commande n\'a toujours pas été livrée alors que le délai estimé était le 27 mai...',
    summaryAr: 'مرحباً، لم يتم تسليم طلبي حتى الآن...',
    relatedOrderId: 'MAY-2026-001842',
    unreadCount: 2,
    priority: 'Normale',
    userEmail: 'a•••••••e@gmail.com',
    preferredChannel: 'Email',
    attachments: [
      { id: 'att-1', name: 'Capture d\'écran 2026-05-28 à 10.20.14.png', size: '1,2 Mo', type: 'image' },
    ],
    messages: [
      {
        id: 'msg-1',
        sender: 'user',
        senderName: 'Vous',
        timestamp: '28 mai 2026 à 10:24',
        text: 'Bonjour, ma commande n\'a toujours pas été livrée alors que le délai estimé était le 27 mai. Pouvez-vous m\'aider à comprendre où en est mon colis ? Merci.',
        attachments: [
          { id: 'att-1', name: 'Capture d\'écran 2026-05-28 à 10.20.14.png', size: '1,2 Mo', type: 'image' },
        ],
      },
      {
        id: 'msg-2',
        sender: 'agent',
        senderName: 'Support Mayush',
        timestamp: '28 mai 2026 à 11:02',
        text: 'Bonjour,\nMerci pour votre message. Nous sommes désolés pour ce retard. Votre colis a été pris en charge par le transporteur et est en cours d\'acheminement. La livraison est prévue d\'ici le 30 mai. Nous vous tiendrons informé dès qu\'il sera livré.\nMerci pour votre patience.',
        statusBadge: 'Répondu',
        attachments: [
          { id: 'att-2', name: 'Preuve d\'expédition_MAY-2026-001842.pdf', size: '452 Ko', type: 'document' },
        ],
      },
    ],
  },
  {
    id: 'req-2',
    reference: 'SUP-2026-001615',
    title: 'Article endommagé à la réception',
    titleAr: 'منتج متضرر عند الاستلام',
    categoryId: 'commandes',
    categoryLabel: 'Commandes & Livraison',
    status: 'in-progress',
    statusLabel: 'En attente',
    statusLabelAr: 'قيد الانتظار',
    date: '18 mai 2026 à 15:38',
    summary: 'Bonjour, le colis est arrivé mais le pied de la table basse présente une rayure importante.',
    summaryAr: 'مرحباً، وصل الطرد لكن طاولة القهوة بها خدش كبيير.',
    relatedOrderId: undefined,
    unreadCount: 1,
    priority: 'Normale',
    userEmail: 'a•••••••e@gmail.com',
    preferredChannel: 'Email',
    messages: [
      {
        id: 'msg-201',
        sender: 'user',
        senderName: 'Vous',
        timestamp: '18 mai 2026 à 15:38',
        text: 'Bonjour, le colis est arrivé mais le pied de la table basse présente une rayure importante.',
      },
      {
        id: 'msg-202',
        sender: 'agent',
        senderName: 'Support Mayush',
        timestamp: '18 mai 2026 à 16:15',
        text: 'Bonjour, nous avons bien reçu votre demande. Pouvez-vous nous envoyer une photo du dégât ?',
        statusBadge: 'En attente',
      },
    ],
  },
  {
    id: 'req-3',
    reference: 'SUP-2026-001257',
    title: 'Demande d\'annulation de commande',
    titleAr: 'طلب إلغاء الطلب',
    categoryId: 'commandes',
    categoryLabel: 'Commandes & Livraison',
    status: 'resolved',
    statusLabel: 'Résolu',
    statusLabelAr: 'تم الحل',
    date: '10 mai 2026 à 09:12',
    summary: 'Annulation effectuée et remboursement validé.',
    summaryAr: 'تم الإلغاء وتأكيد الاسترداد.',
    relatedOrderId: undefined,
    unreadCount: 0,
    priority: 'Normale',
    messages: [
      {
        id: 'msg-301',
        sender: 'user',
        senderName: 'Vous',
        timestamp: '10 mai 2026 à 09:12',
        text: 'Bonjour, je souhaite annuler ma commande car j\'ai changé d\'avis.',
      },
      {
        id: 'msg-302',
        sender: 'agent',
        senderName: 'Support Mayush',
        timestamp: '10 mai 2026 à 10:00',
        text: 'Votre commande a été annulée avec succès et le remboursement a été initié.',
        statusBadge: 'Résolu',
      },
    ],
  },
  {
    id: 'req-4',
    reference: 'SUP-2026-000983',
    title: 'Question sur les matériaux',
    titleAr: 'سؤال حول المواد',
    categoryId: 'produits',
    categoryLabel: 'Produits & Disponibilité',
    status: 'resolved',
    statusLabel: 'Résolu',
    statusLabelAr: 'تم الحل',
    date: '05 mai 2026 à 11:45',
    summary: 'Renseignements sur le bois du canapé Dune.',
    summaryAr: 'معلومات حول خشب أريكة الكثبان.',
    relatedOrderId: undefined,
    unreadCount: 0,
    priority: 'Normale',
    messages: [
      {
        id: 'msg-401',
        sender: 'user',
        senderName: 'Vous',
        timestamp: '05 mai 2026 à 11:45',
        text: 'Est-ce que la structure du canapé est en chêne massif ?',
      },
      {
        id: 'msg-402',
        sender: 'agent',
        senderName: 'Support Mayush',
        timestamp: '05 mai 2026 à 12:30',
        text: 'Oui, la structure est en chêne massif certifié FSC.',
        statusBadge: 'Résolu',
      },
    ],
  },
];

const CONTACT_CHANNELS: ContactChannel[] = [
  { id: 'chan-1', type: 'phone', label: 'Téléphone', labelAr: 'الهاتف', value: '+212 5 22 00 11 22', icon: 'phone' },
  { id: 'chan-2', type: 'email', label: 'Email', labelAr: 'البريد الإلكتروني', value: 'support@mayush.ma', icon: 'mail' },
  { id: 'chan-3', type: 'chat', label: 'WhatsApp', labelAr: 'واتساب', value: '+212 6 00 11 22 33', icon: 'message-square' },
];

// ── State Manager Singleton ──

class SupportStateManager {
  private static instance: SupportStateManager;

  private faqCategories: FaqCategory[] = [...FAQ_CATEGORIES];
  private faqItems: FaqItem[] = [...FAQ_ITEMS];
  private supportRequests: SupportRequest[] = [...SEEDED_SUPPORT_REQUESTS];
  private contactChannels: ContactChannel[] = [...CONTACT_CHANNELS];

  private selectedFaqId: string = '';
  private selectedFaqCategory: string = '';
  private selectedSupportRequestId: string = 'req-1';
  private searchQuery: string = '';
  private selectedHelpCategory: string = '';
  private selectedArticleId: string = 'faq-1';

  // Draft states
  private contactDraft: ContactDraft = {
    subject: '',
    categoryId: 'commandes',
    message: '',
    email: 'a•••••••e@gmail.com',
    preferredChannel: 'Email',
    attachments: [],
  };

  private replyDraft: ReplyDraft = {
    ticketId: '',
    message: '',
    attachments: [],
  };

  private listeners: (() => void)[] = [];

  private constructor() {}

  public static getInstance(): SupportStateManager {
    if (!SupportStateManager.instance) {
      SupportStateManager.instance = new SupportStateManager();
    }
    return SupportStateManager.instance;
  }

  public subscribe(listener: () => void): () => void {
    this.listeners.push(listener);
    return () => {
      this.listeners = this.listeners.filter((l) => l !== listener);
    };
  }

  private notify() {
    this.listeners.forEach((l) => l());
  }

  // ── FAQ ──

  public getFaqCategories(): FaqCategory[] {
    return [...this.faqCategories];
  }

  public getFaqItems(): FaqItem[] {
    return [...this.faqItems];
  }

  public getFaqItemsByCategory(categoryId: string): FaqItem[] {
    if (!categoryId || categoryId === 'toutes') return [...this.faqItems];
    return this.faqItems.filter((item) => item.categoryId === categoryId);
  }

  public getFaqItemById(id: string): FaqItem | undefined {
    return this.faqItems.find((item) => item.id === id);
  }

  public getSelectedFaqId(): string {
    return this.selectedFaqId;
  }

  public setSelectedFaqId(id: string) {
    this.selectedFaqId = id;
    this.notify();
  }

  public getSelectedFaqCategory(): string {
    return this.selectedFaqCategory;
  }

  public setSelectedFaqCategory(categoryId: string) {
    this.selectedFaqCategory = categoryId;
    this.notify();
  }

  // ── Search & Selection ──

  public getSearchQuery(): string {
    return this.searchQuery;
  }

  public setSearchQuery(query: string) {
    this.searchQuery = query;
    this.notify();
  }

  public getSelectedHelpCategory(): string {
    return this.selectedHelpCategory;
  }

  public setSelectedHelpCategory(categoryId: string) {
    this.selectedHelpCategory = categoryId;
    this.notify();
  }

  public getSelectedArticleId(): string {
    return this.selectedArticleId;
  }

  public setSelectedArticleId(id: string) {
    this.selectedArticleId = id;
    this.notify();
  }

  public searchHelp(query: string = this.searchQuery): SearchResult {
    const q = query.trim().toLowerCase();
    if (!q) {
      return {
        articles: [...this.faqItems],
        categories: [...this.faqCategories],
        totalResults: this.faqItems.length + this.faqCategories.length,
      };
    }

    const articles = this.faqItems.filter((item) =>
      item.question.toLowerCase().includes(q) ||
      item.answer.toLowerCase().includes(q) ||
      item.questionAr.includes(q) ||
      item.answerAr.includes(q) ||
      (item.subtitle && item.subtitle.toLowerCase().includes(q)) ||
      (item.subtitleAr && item.subtitleAr.includes(q)) ||
      item.categoryId.toLowerCase().includes(q)
    );

    const categories = this.faqCategories.filter((cat) =>
      cat.label.toLowerCase().includes(q) ||
      cat.labelAr.includes(q) ||
      cat.id.toLowerCase().includes(q) ||
      (cat.subtitleFr && cat.subtitleFr.toLowerCase().includes(q))
    );

    return {
      articles,
      categories,
      totalResults: articles.length + categories.length,
    };
  }

  // ── Support Requests & Tickets ──

  public getSupportRequests(): SupportRequest[] {
    return [...this.supportRequests];
  }

  public getSupportRequestById(id: string): SupportRequest | undefined {
    return this.supportRequests.find((r) => r.id === id || r.reference === id);
  }

  public filterSupportRequests(statusFilter?: string, query?: string): SupportRequest[] {
    let list = [...this.supportRequests];

    if (statusFilter && statusFilter !== 'toutes') {
      if (statusFilter === 'ouvertes') {
        list = list.filter((r) => r.status === 'open');
      } else if (statusFilter === 'attente') {
        list = list.filter((r) => r.status === 'in-progress');
      } else if (statusFilter === 'resolues') {
        list = list.filter((r) => r.status === 'resolved' || r.status === 'closed');
      }
    }

    if (query && query.trim()) {
      const q = query.trim().toLowerCase();
      list = list.filter((r) =>
        r.reference.toLowerCase().includes(q) ||
        r.title.toLowerCase().includes(q) ||
        r.titleAr.includes(q) ||
        (r.relatedOrderId && r.relatedOrderId.toLowerCase().includes(q))
      );
    }

    return list;
  }

  public getSelectedSupportRequestId(): string {
    return this.selectedSupportRequestId;
  }

  public setSelectedSupportRequestId(id: string) {
    this.selectedSupportRequestId = id;
    // Clear unread badge on open
    const req = this.supportRequests.find((r) => r.id === id);
    if (req) {
      req.unreadCount = 0;
    }
    this.notify();
  }

  public createSupportRequest(draft: ContactDraft): SupportRequest {
    const nextNum = Math.floor(1000 + Math.random() * 9000);
    const ref = `SUP-2026-00${nextNum}`;
    const now = new Date();
    const dateStr = `${now.getDate()} mai 2026 à ${now.getHours()}:${now.getMinutes() < 10 ? '0' : ''}${now.getMinutes()}`;

    const catObj = this.faqCategories.find((c) => c.id === draft.categoryId);

    const newReq: SupportRequest = {
      id: `req-${Date.now()}`,
      reference: ref,
      title: draft.subject || 'Demande d\'assistance',
      titleAr: 'طلب مساعدة',
      categoryId: draft.categoryId,
      categoryLabel: catObj ? catObj.label : 'Support Client',
      status: 'open',
      statusLabel: 'Ouvert',
      statusLabelAr: 'مفتوح',
      date: dateStr,
      summary: draft.message.substring(0, 100) + '...',
      summaryAr: draft.message.substring(0, 100) + '...',
      relatedOrderId: draft.selectedOrderId || undefined,
      unreadCount: 0,
      priority: 'Normale',
      userEmail: draft.email,
      preferredChannel: draft.preferredChannel,
      attachments: [...draft.attachments],
      messages: [
        {
          id: `msg-${Date.now()}`,
          sender: 'user',
          senderName: 'Vous',
          timestamp: dateStr,
          text: draft.message,
          attachments: [...draft.attachments],
        },
      ],
    };

    this.supportRequests.unshift(newReq);
    this.selectedSupportRequestId = newReq.id;
    this.clearContactDraft();
    this.notify();
    return newReq;
  }

  public addReplyToRequest(ticketId: string, messageText: string, attachments: SupportAttachment[] = []): SupportMessage | undefined {
    const req = this.supportRequests.find((r) => r.id === ticketId || r.reference === ticketId);
    if (!req) return undefined;

    const now = new Date();
    const dateStr = `${now.getDate()} mai 2026 à ${now.getHours()}:${now.getMinutes() < 10 ? '0' : ''}${now.getMinutes()}`;

    const newMsg: SupportMessage = {
      id: `msg-${Date.now()}`,
      sender: 'user',
      senderName: 'Vous',
      timestamp: dateStr,
      text: messageText,
      attachments: [...attachments],
    };

    req.messages.push(newMsg);
    req.status = 'open';
    req.statusLabel = 'Ouvert';
    req.statusLabelAr = 'مفتوح';

    this.clearReplyDraft();
    this.notify();
    return newMsg;
  }

  public closeSupportRequest(ticketId: string): boolean {
    const req = this.supportRequests.find((r) => r.id === ticketId || r.reference === ticketId);
    if (!req) return false;

    req.status = 'resolved';
    req.statusLabel = 'Résolu';
    req.statusLabelAr = 'تم الحل';
    this.notify();
    return true;
  }

  public rateTicket(ticketId: string, stars: number, comment?: string): boolean {
    const req = this.supportRequests.find((r) => r.id === ticketId || r.reference === ticketId);
    if (!req) return false;

    const now = new Date();
    const dateStr = `${now.getDate()} mai 2026 à ${now.getHours()}:${now.getMinutes() < 10 ? '0' : ''}${now.getMinutes()}`;

    req.rating = {
      stars,
      comment,
      ratedAt: dateStr,
    };
    this.notify();
    return true;
  }

  // ── Draft Management ──

  public getContactDraft(): ContactDraft {
    return { ...this.contactDraft, attachments: [...this.contactDraft.attachments] };
  }

  public setContactDraft(draft: Partial<ContactDraft>) {
    this.contactDraft = { ...this.contactDraft, ...draft };
    this.notify();
  }

  public updateContactDraft(updater: (prev: ContactDraft) => ContactDraft) {
    this.contactDraft = updater(this.contactDraft);
    this.notify();
  }

  public clearContactDraft() {
    this.contactDraft = {
      subject: '',
      categoryId: 'commandes',
      message: '',
      email: 'a•••••••e@gmail.com',
      preferredChannel: 'Email',
      selectedOrderId: undefined,
      returnRequestId: undefined,
      refundId: undefined,
      attachments: [],
    };
    this.notify();
  }

  public getReplyDraft(): ReplyDraft {
    return { ...this.replyDraft, attachments: [...this.replyDraft.attachments] };
  }

  public setReplyDraft(draft: Partial<ReplyDraft>) {
    this.replyDraft = { ...this.replyDraft, ...draft };
    this.notify();
  }

  public clearReplyDraft() {
    this.replyDraft = {
      ticketId: '',
      message: '',
      attachments: [],
    };
    this.notify();
  }

  // ── Contact Channels ──

  public getContactChannels(): ContactChannel[] {
    return [...this.contactChannels];
  }

  // ── Reset ──

  public reset() {
    this.selectedFaqId = '';
    this.selectedFaqCategory = '';
    this.selectedSupportRequestId = 'req-1';
    this.searchQuery = '';
    this.selectedHelpCategory = '';
    this.selectedArticleId = 'faq-1';
    this.supportRequests = [...SEEDED_SUPPORT_REQUESTS];
    this.clearContactDraft();
    this.clearReplyDraft();
    this.notify();
  }
}

export const supportState = SupportStateManager.getInstance();

export const getSupportSelectableOrderIds = (
  repository: BuyerOrderRepository = orderState,
): string[] => repository.getOrders().map((order) => order.orderId);
