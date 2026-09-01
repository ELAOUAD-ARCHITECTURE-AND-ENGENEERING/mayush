export type SupportAvailabilityStatus = 'online' | 'connection_error' | 'unavailable';

export interface ImpactedService {
  id: string;
  name: string;
  nameAr: string;
  icon: string;
  status: 'unavailable' | 'degraded' | 'available';
  statusLabel: string;
  statusLabelAr: string;
}

export interface MaintenanceStateInfo {
  isMaintenanceMode: boolean;
  title: string;
  titleAr: string;
  description: string;
  descriptionAr: string;
  lastCheckedTimestamp: string;
  impactedServices: ImpactedService[];
}

export interface AppUpdateInfo {
  updateAvailable: boolean;
  currentVersion: string;
  latestVersion: string;
  isMandatory: boolean;
  releaseNotes: string[];
  releaseNotesAr: string[];
}

class SystemStateManager {
  private static instance: SystemStateManager;

  private supportAvailabilityStatus: SupportAvailabilityStatus = 'online';

  private maintenanceInfo: MaintenanceStateInfo = {
    isMaintenanceMode: true,
    title: 'Nous améliorons votre expérience',
    titleAr: 'نحن نعمل على تحسين تجربتك',
    description:
      'Nous effectuons actuellement une maintenance pour vous offrir un service encore plus rapide, fiable et sécurisé. Veuillez réessayer dans quelques instants.',
    descriptionAr:
      'نقوم حالياً بإجراء صيانة لتقديم خدمة أسرع وأكثر أماناً وموثوقية. يرجى إعادة المحاولة بعد لحظات.',
    lastCheckedTimestamp: '28 mai 2026 à 10:24',
    impactedServices: [
      {
        id: 'orders',
        name: 'Passation de commandes',
        nameAr: 'إتمام الطلبات',
        icon: 'shopping-bag',
        status: 'unavailable',
        statusLabel: 'Indisponible',
        statusLabelAr: 'غير متاح',
      },
      {
        id: 'tracking',
        name: 'Suivi des livraisons',
        nameAr: 'متابعة الشحنات',
        icon: 'truck',
        status: 'unavailable',
        statusLabel: 'Indisponible',
        statusLabelAr: 'غير متاح',
      },
      {
        id: 'history',
        name: 'Historique des commandes',
        nameAr: 'سجل الطلبات',
        icon: 'file-text',
        status: 'unavailable',
        statusLabel: 'Indisponible',
        statusLabelAr: 'غير متاح',
      },
    ],
  };

  private updateInfo: AppUpdateInfo = {
    updateAvailable: true,
    currentVersion: '1.0.0',
    latestVersion: '1.3.0',
    isMandatory: false,
    releaseNotes: [
      'Améliorations de performance',
      'Corrections de bugs',
      'Expérience utilisateur optimisée',
    ],
    releaseNotesAr: [
      'تحسينات في الأداء',
      'إصلاح الأخطاء',
      'تحسين تجربة المستخدم',
    ],
  };

  private settingsLoadState: 'idle' | 'loading' | 'error' | 'ready' = 'ready';

  private listeners: (() => void)[] = [];

  private constructor() {}

  public static getInstance(): SystemStateManager {
    if (!SystemStateManager.instance) {
      SystemStateManager.instance = new SystemStateManager();
    }
    return SystemStateManager.instance;
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

  public getSupportAvailabilityStatus(): SupportAvailabilityStatus {
    return this.supportAvailabilityStatus;
  }

  public setSupportAvailabilityStatus(status: SupportAvailabilityStatus) {
    this.supportAvailabilityStatus = status;
    this.notify();
  }

  public getMaintenanceInfo(): MaintenanceStateInfo {
    return {
      ...this.maintenanceInfo,
      impactedServices: [...this.maintenanceInfo.impactedServices],
    };
  }

  public getAppUpdateInfo(): AppUpdateInfo {
    return {
      ...this.updateInfo,
      releaseNotes: [...this.updateInfo.releaseNotes],
      releaseNotesAr: [...this.updateInfo.releaseNotesAr],
    };
  }

  public setUpdateMode(mode: 'optional' | 'forced') {
    this.updateInfo.isMandatory = mode === 'forced';
    this.notify();
  }

  public getSettingsLoadState(): 'idle' | 'loading' | 'error' | 'ready' {
    return this.settingsLoadState;
  }

  public setSettingsLoadState(state: 'idle' | 'loading' | 'error' | 'ready') {
    this.settingsLoadState = state;
    this.notify();
  }

  public retrySettingsLoad() {
    this.settingsLoadState = 'loading';
    this.notify();
    setTimeout(() => {
      this.settingsLoadState = 'ready';
      this.notify();
    }, 1200);
  }
}

export const systemState = SystemStateManager.getInstance();
