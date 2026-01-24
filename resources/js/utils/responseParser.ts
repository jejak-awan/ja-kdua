/**
 * Response Parser Utility
 * 
 * Provides consistent parsing of API responses from BaseApiController.
 * Handles both paginated and non-paginated responses.
 */

import { AxiosResponse } from 'axios';

export interface PaginationData {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    per_page?: number;
}

export interface ParsedResponse<T = any> {
    data: T;
    pagination: PaginationData | null;
}

/**
 * Parse API response data
 * Handles BaseApiController response structure
 */
export function parseResponse<T = any>(response: AxiosResponse): ParsedResponse<T> {
    let data: any = [];
    let pagination: PaginationData | null = null;

    // Helper to extract pagination from an object
    const extractPagination = (obj: any): PaginationData | null => {
        if (obj.pagination) return obj.pagination;
        if (obj.meta?.pagination) return obj.meta.pagination;
        if (obj.current_page) {
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

    // Handle paginated response from BaseApiController
    if (response.data?.data?.data && Array.isArray(response.data.data.data)) {
        data = response.data.data.data;
        pagination = extractPagination(response.data.data);
    }
    // Handle simple success response or where data is the array
    else if (response.data?.data && Array.isArray(response.data.data)) {
        data = response.data.data;
        pagination = extractPagination(response.data);
    }
    // Handle direct array response (fallback)
    else if (Array.isArray(response.data)) {
        data = response.data;
    }
    // Handle alternative paginated format (items key)
    else if (response.data?.items && Array.isArray(response.data.items)) {
        data = response.data.items;
        pagination = extractPagination(response.data);
    }
    // Handle single object response
    else if (response.data?.data && !Array.isArray(response.data.data)) {
        data = [response.data.data];
    }

    return { data, pagination };
}

/**
 * Parse single object response
 */
export function parseSingleResponse<T = any>(response: AxiosResponse): T | null {
    if (response.data?.data) {
        return response.data.data;
    }
    return response.data || null;
}

/**
 * Parse pagination info from response
 */
export function parsePagination(response: AxiosResponse): PaginationData | null {
    // From BaseApiController.paginated()
    if (response.data?.data?.pagination) {
        return response.data.data.pagination;
    }
    // From direct paginated response
    if (response.data?.current_page) {
        return {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
        };
    }
    return null;
}

/**
 * Ensure value is an array
 */
export function ensureArray<T = any>(value: any): T[] {
    if (Array.isArray(value)) {
        return value;
    }
    return [];
}
