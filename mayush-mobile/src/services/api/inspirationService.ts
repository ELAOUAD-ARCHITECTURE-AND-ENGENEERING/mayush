import { API_BASE_URL } from '../../api';

export interface InspirationPreview {
  id: number;
  slug: string;
  title: string;
  subtitle: string;
  image: string;
  products_count: number;
  preview_products: InspirationPreviewProduct[];
}

export interface InspirationPreviewProduct {
  id: number;
  name: string;
  image: string;
  price: string;
  available: boolean;
}

export interface InspirationDetail {
  id: number;
  slug: string;
  title: string;
  subtitle: string;
  description: string;
  image: {
    url: string;
    width: number;
    height: number;
  };
  items: InspirationDetailItem[];
}

export interface InspirationDetailItem {
  id: number;
  display_order: number;
  hotspot: { x: number; y: number } | null;
  product: {
    id: number;
    name: string;
    slug: string;
    price: string;
    discount_price: string | null;
    image: string;
    available: boolean;
    stock_status: string;
  };
}

export const inspirationService = {
  async getFeatured(language: string): Promise<InspirationPreview[]> {
    try {
      const response = await fetch(`${API_BASE_URL}/inspirations/featured`, {
        headers: {
          'Accept': 'application/json',
          'Accept-Language': language,
        },
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const json = await response.json();
      return json.data || [];
    } catch {
      return [];
    }
  },

  async getAll(language: string): Promise<InspirationPreview[]> {
    try {
      const response = await fetch(`${API_BASE_URL}/inspirations`, {
        headers: {
          'Accept': 'application/json',
          'Accept-Language': language,
        },
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const json = await response.json();
      return json.data || [];
    } catch {
      return [];
    }
  },

  async getBySlug(slug: string, language: string): Promise<InspirationDetail | null> {
    try {
      const response = await fetch(`${API_BASE_URL}/inspirations/${encodeURIComponent(slug)}`, {
        headers: {
          'Accept': 'application/json',
          'Accept-Language': language,
        },
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const json = await response.json();
      return json.data || null;
    } catch {
      return null;
    }
  },
};
