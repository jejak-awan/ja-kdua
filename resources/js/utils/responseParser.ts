import type { AxiosResponse } from 'axios';

export interface PaginationData {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    per_page?: number;
}

/**
 * Standard API Response structure from BaseApiController
 */
export interface BaseApiResponse<T> {
    success: boolean;
    message?: string;
    data: T;
}

/**
 * Parsed structure for frontend consume
 */
export interface ParsedResponse<T> {
    data: T[];
    pagination: PaginationData | null;
}

/**
 * Parse API response data
 * Handles BaseApiController response structure for lists/collections
 */
export function parseResponse<T>(response: AxiosResponse<any>): ParsedResponse<T> {
    let data: T[] = [];
    let pagination: PaginationData | null = null;

    const responseData = response.data;
    if (!responseData) return { data: [], pagination: null };

    // Helper to extract pagination from an object
    const extractPagination = (obj: any): PaginationData | null => {
        if (!obj) return null;
        if (obj.pagination) return obj.pagination;
        if (obj.meta?.pagination) return obj.meta.pagination;
        if (typeof obj.current_page === 'number') {
            return {
                current_page: obj.current_page,
                last_page: obj.last_page,
                from: obj.from,
                to: obj.to,
                total: obj.total,
                per_page: obj.per_page
            };
        }
        return null;
    };

    // Case 1: Data is nested inside success wrapper (standard Laravel pattern)
    // response.data.data.data (paginated) or response.data.data (direct)
    const inner = responseData.data;

    if (inner) {
        if (Array.isArray(inner.data)) {
            data = inner.data;
            pagination = extractPagination(inner);
        } else if (Array.isArray(inner)) {
            data = inner;
            pagination = extractPagination(responseData);
        } else {
            // Single object wrapped in data
            data = [inner as T];
        }
    }
    // Case 2: Direct array or items
    else if (Array.isArray(responseData)) {
        data = responseData;
    } else if (Array.isArray(responseData.items)) {
        data = responseData.items;
        pagination = extractPagination(responseData);
    }

    return { data, pagination };
}

/**
 * Parse single object response
 */
export function parseSingleResponse<T>(response: AxiosResponse<any>): T | null {
    if (!response.data) return null;

    // Check if it's wrapped in a 'data' property
    if (Object.prototype.hasOwnProperty.call(response.data, 'data')) {
        return response.data.data as T;
    }

    return response.data as T;
}

/**
 * Parse pagination info from response
 */
export function parsePagination(response: AxiosResponse<any>): PaginationData | null {
    const d = response.data;
    if (!d) return null;

    if (d.data?.pagination) return d.data.pagination;
    if (d.pagination) return d.pagination;

    if (typeof d.current_page === 'number') {
        return {
            current_page: d.current_page,
            last_page: d.last_page,
            from: d.from,
            to: d.to,
            total: d.total,
        };
    }
    return null;
}

/**
 * Ensure value is an array
 */
export function ensureArray<T>(value: unknown): T[] {
    if (Array.isArray(value)) {
        return value as T[];
    }
    return [];
}
