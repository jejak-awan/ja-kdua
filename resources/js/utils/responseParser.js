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

    // Helper to extract pagination from an object
    const extractPagination = (obj) => {
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

    // Handle paginated response from BaseApiController where data is a Paginator object
    // Structure: { success: true, data: { data: [...], current_page: 1, ... } }
    // OR Structure: { success: true, data: { data: [...], pagination: {...} } }
    if (response.data?.data?.data && Array.isArray(response.data.data.data)) {
        data = response.data.data.data;
        pagination = extractPagination(response.data.data);
    }
    // Handle simple success response or where data is the array and pagination is sibling
    // Structure: { success: true, data: [...], meta: { current_page: 1... } }
    // OR Structure: { data: [...], current_page: 1... } (Paginator at root)
    else if (response.data?.data && Array.isArray(response.data.data)) {
        data = response.data.data;
        // Check if response.data itself has pagination info (common in Resources)
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

