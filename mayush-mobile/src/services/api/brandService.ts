import { apiClient } from '../../api';
import { normalizeImageUrl } from '../../contracts/mappers/imageNormalizer';
import { MvpAppLanguage } from '../../contracts/api/dto';

export interface BrandDto {
  id: number;
  name: string;
  logo: string;
  slug?: string;
}

export const brandService = {
  /**
   * GET /api/v2/brands/top
   */
  async getTopBrands(language: MvpAppLanguage = 'fr'): Promise<BrandDto[]> {
    try {
      const res = await apiClient<{ data: BrandDto[] }>('/api/v2/brands/top', { language });
      if (res && Array.isArray(res.data)) {
        return res.data.map((b) => ({
          ...b,
          logo: normalizeImageUrl(b.logo),
        }));
      }
    } catch {
      // Fail silently — partners section is non-critical
    }
    return [];
  },

  /**
   * GET /api/v2/all-brands
   */
  async getAllBrands(language: MvpAppLanguage = 'fr'): Promise<BrandDto[]> {
    try {
      const res = await apiClient<{ data: BrandDto[] }>('/api/v2/all-brands', { language });
      if (res && Array.isArray(res.data)) {
        return res.data.map((b) => ({
          ...b,
          logo: normalizeImageUrl(b.logo),
        }));
      }
    } catch {
      // Fail silently
    }
    return [];
  },
};
