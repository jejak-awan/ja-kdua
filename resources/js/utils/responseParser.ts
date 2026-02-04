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
export function parseResponse<T>(response: AxiosResponse<unknown>): ParsedResponse<T> {
    let data: T[] = [];
    let pagination: PaginationData | null = null;

    const responseData = response.data;
    if (!responseData) return { data: [], pagination: null };

    // Helper to extract pagination from an object
    const extractPagination = (obj: unknown): PaginationData | null => {
        if (!obj || typeof obj !== 'object') return null;
        const data = obj as Record<string, unknown>;
        if (data.pagination) return data.pagination as PaginationData;
        if ((data.meta as Record<string, unknown>)?.pagination) return (data.meta as Record<string, unknown>).pagination as PaginationData;
        if (typeof data.current_page === 'number') {
            return {
                current_page: data.current_page,
                last_page: data.last_page as number,
                from: data.from as number,
                to: data.to as number,
                total: data.total as number,
                per_page: data.per_page as number
            };
        }
        return null;
    };

    // Case 1: Data is nested inside success wrapper (standard Laravel pattern)
    // response.data.data.data (paginated) or response.data.data (direct)
    const responseDataObj = responseData as Record<string, unknown>;
    const inner = responseDataObj.data as Record<string, unknown>;

    if (inner) {
        if (Array.isArray(inner.data)) {
            data = inner.data;
            pagination = extractPagination(inner);
        } else if (Array.isArray(inner)) {
            data = inner as T[];
            pagination = extractPagination(responseDataObj);
        } else {
            // Single object wrapped in data
            data = [inner as unknown as T];
        }
    }
    // Case 2: Direct array or items
    else if (Array.isArray(responseDataObj)) {
        data = responseDataObj as unknown as T[];
    } else if (Array.isArray(responseDataObj.items)) {
        data = responseDataObj.items as T[];
        pagination = extractPagination(responseDataObj);
    }

    return { data, pagination };
}

/**
 * Parse single object response
 */
export function parseSingleResponse<T>(response: AxiosResponse<unknown>): T | null {
    if (!response.data || typeof response.data !== 'object') return null;

    const data = response.data as Record<string, unknown>;

    // Check if it's wrapped in a 'data' property
    if (Object.prototype.hasOwnProperty.call(data, 'data')) {
        return data.data as T;
    }

    return data as unknown as T;
}

/**
 * Parse pagination info from response
 */
export function parsePagination(response: AxiosResponse<unknown>): PaginationData | null {
    const d = response.data as Record<string, unknown>;
    if (!d || typeof d !== 'object') return null;

    if ((d.data as Record<string, unknown>)?.pagination) return (d.data as Record<string, unknown>).pagination as PaginationData;
    if (d.pagination) return d.pagination as PaginationData;

    if (typeof d.current_page === 'number') {
        return {
            current_page: d.current_page,
            last_page: d.last_page as number,
            from: d.from as number,
            to: d.to as number,
            total: d.total as number,
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
