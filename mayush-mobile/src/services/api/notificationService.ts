/**
 * Mayush Mobile Notification API Service
 * Handles notification queries including unread count and summary
 * with the Laravel backend.
 */

import { apiClient } from './apiClient';

export interface NotificationSummaryDto {
  unread_count: number;
  total: number;
}

export const notificationService = {
  /**
   * GET /api/v2/unread-notifications
   * Requires auth:sanctum
   */
  async getUnreadCount(): Promise<number> {
    try {
      const res = await apiClient<{ count?: number; data?: any[] }>('/api/v2/unread-notifications');
      if (res && typeof res.count === 'number') return res.count;
      if (res && Array.isArray(res.data)) return res.data.length;
      return 0;
    } catch {
      return 0;
    }
  },

  /**
   * GET /api/v2/notifications/summary
   */
  async getSummary(): Promise<NotificationSummaryDto> {
    try {
      const res = await apiClient<NotificationSummaryDto>('/api/v2/notifications/summary');
      return { unread_count: res?.unread_count ?? 0, total: res?.total ?? 0 };
    } catch {
      return { unread_count: 0, total: 0 };
    }
  },
};
