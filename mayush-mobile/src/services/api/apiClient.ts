/**
 * Mayush Mobile API Client
 * Centralized HTTP fetch client strictly implementing headers, query parameters,
 * authentication Bearer tokens, and error normalization per locked specifications.
 * Includes timeout controller for fast fallback recovery when local server is unreachable.
 */

import { MvpAppLanguage } from '../../contracts/api/dto';

const configuredApiBaseUrl = process.env.EXPO_PUBLIC_API_BASE_URL?.trim();

/**
 * The mobile client reads the currently deployed Laravel origin from its own
 * public environment. Local development intentionally defaults to the active
 * Laragon host; production must provide EXPO_PUBLIC_API_BASE_URL at build time.
 */
export const API_BASE_URL = (configuredApiBaseUrl || 'http://mayush.test').replace(/\/$/, '');

export interface RequestOptions {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
  headers?: Record<string, string>;
  params?: Record<string, string | number | undefined>;
  body?: any;
  token?: string;
  language?: MvpAppLanguage;
  timeoutMs?: number;
}

export async function apiClient<T>(
  endpoint: string,
  options: RequestOptions = {}
): Promise<T> {
  const {
    method = 'GET',
    headers: customHeaders = {},
    params,
    body,
    token,
    language = 'fr',
    timeoutMs = 2500,
  } = options;

  let url = endpoint.startsWith('http') ? endpoint : `${API_BASE_URL}${endpoint}`;

  if (params) {
    const searchParams = new URLSearchParams();
    Object.entries(params).forEach(([key, val]) => {
      if (val !== undefined && val !== null) {
        searchParams.append(key, String(val));
      }
    });
    const queryString = searchParams.toString();
    if (queryString) {
      url += (url.includes('?') ? '&' : '?') + queryString;
    }
  }

  const headers: Record<string, string> = {
    'Accept': 'application/json',
    'App-Language': language,
    ...customHeaders,
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  let requestBody: string | undefined = undefined;

  // Include Content-Type: application/json ONLY when JSON request body is present
  if (body !== undefined && body !== null) {
    headers['Content-Type'] = 'application/json';
    requestBody = typeof body === 'string' ? body : JSON.stringify(body);
  }

  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), timeoutMs);

  try {
    const response = await fetch(url, {
      method,
      headers,
      body: requestBody,
      signal: controller.signal,
    });

    clearTimeout(timer);

    const contentType = response.headers.get('content-type');
    let data: any;

    if (contentType && contentType.includes('application/json')) {
      data = await response.json();
    } else {
      data = await response.text();
    }

    if (!response.ok) {
      const errorMessage =
        data && typeof data === 'object' && data.message
          ? data.message
          : `HTTP ${response.status}: Request failed`;
      throw new Error(typeof errorMessage === 'string' ? errorMessage : JSON.stringify(errorMessage));
    }

    return data as T;
  } catch (err: any) {
    clearTimeout(timer);
    throw err;
  }
}
