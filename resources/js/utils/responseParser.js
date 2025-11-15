/**
 * Response Parser Utility
 * 
 * Provides consistent parsing of API responses from BaseApiController.
 * Handles both paginated and non-paginated responses.
 */

/**
 * Parse API response data
 * Handles BaseApiController response structure:
 * - Success response: { success: true, message: "...", data: [...] }
 * - Paginated response: { success: true, message: "...", data: { data: [...], pagination: {...} } }
 * 
 * @param {Object} response - Axios response object
 * @returns {Object} - { data: Array, pagination: Object|null }
 */
export function parseResponse(response) {
    let data = [];
    let pagination = null;

    // Handle paginated response from BaseApiController.paginated()
    if (response.data?.data?.data && Array.isArray(response.data.data.data)) {
        // Paginated: { success: true, data: { data: [...], pagination: {...} } }
        data = response.data.data.data;
        if (response.data.data.pagination) {
            pagination = response.data.data.pagination;
        }
    } 
    // Handle simple success response from BaseApiController.success()
    else if (response.data?.data && Array.isArray(response.data.data)) {
        // Simple array: { success: true, data: [...] }
        data = response.data.data;
    } 
    // Handle direct array response (fallback)
    else if (Array.isArray(response.data)) {
        data = response.data;
    }
    // Handle alternative paginated format
    else if (response.data?.items && Array.isArray(response.data.items)) {
        data = response.data.items;
        if (response.data.current_page) {
            pagination = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                from: response.data.from,
                to: response.data.to,
                total: response.data.total,
            };
        }
    }
    // Handle single object response
    else if (response.data?.data && !Array.isArray(response.data.data)) {
        data = [response.data.data];
    }

    return { data, pagination };
}

/**
 * Parse single object response
 * 
 * @param {Object} response - Axios response object
 * @returns {Object|null} - Parsed object or null
 */
export function parseSingleResponse(response) {
    if (response.data?.data) {
        return response.data.data;
    }
    return response.data || null;
}

/**
 * Parse pagination info from response
 * 
 * @param {Object} response - Axios response object
 * @returns {Object|null} - Pagination object or null
 */
export function parsePagination(response) {
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
 * 
 * @param {*} value - Value to ensure is array
 * @returns {Array} - Array (empty if value is not array)
 */
export function ensureArray(value) {
    if (Array.isArray(value)) {
        return value;
    }
    return [];
}

