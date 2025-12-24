import api from '../services/api';

/**
 * Analytics tracking composable for frontend event tracking
 */
export function useAnalytics() {
    /**
     * Track a custom event
     * @param {string} eventType - Type of event (click, download, form_submit, etc.)
     * @param {string} eventName - Name of the event
     * @param {object} data - Additional event data
     * @param {number|null} contentId - Related content ID (optional)
     */
    const trackEvent = async (eventType, eventName, data = {}, contentId = null) => {
        try {
            await api.post('/analytics/track', {
                event_type: eventType,
                event_name: eventName,
                event_data: data,
                content_id: contentId,
            });
        } catch (e) {
            // Silently fail - analytics should not break the app
            console.warn('Analytics tracking failed:', e);
        }
    };

    /**
     * Track a click event
     * @param {string} buttonName - Name of the button/link
     * @param {string} url - URL clicked (optional)
     */
    const trackClick = (buttonName, url = null) => {
        return trackEvent('click', `Click: ${buttonName}`, {
            button_name: buttonName,
            url: url || window.location.href,
        });
    };

    /**
     * Track a file download
     * @param {string} fileName - Name of the file
     * @param {string} fileType - File type/extension
     * @param {number|null} mediaId - Media ID if applicable
     */
    const trackDownload = (fileName, fileType = null, mediaId = null) => {
        return trackEvent('download', `Download: ${fileName}`, {
            file_name: fileName,
            file_type: fileType,
            media_id: mediaId,
        });
    };

    /**
     * Track a form submission
     * @param {string} formName - Name of the form
     * @param {object} data - Form data (sanitized, no sensitive info)
     */
    const trackFormSubmit = (formName, data = {}) => {
        return trackEvent('form_submit', `Form Submit: ${formName}`, {
            form_name: formName,
            ...data,
        });
    };

    /**
     * Track a search query
     * @param {string} query - Search query
     * @param {number} resultsCount - Number of results
     */
    const trackSearch = (query, resultsCount = 0) => {
        return trackEvent('search', `Search: ${query}`, {
            query: query,
            results_count: resultsCount,
        });
    };

    /**
     * Track page view (for SPA navigation)
     * @param {string} pageName - Name of the page
     * @param {string} url - Page URL
     */
    const trackPageView = (pageName, url = null) => {
        return trackEvent('page_view', `Page View: ${pageName}`, {
            page_name: pageName,
            url: url || window.location.href,
        });
    };

    /**
     * Track multiple events in batch
     * @param {Array} events - Array of events [{type, name, data, content_id}]
     */
    const trackBatch = async (events) => {
        if (!events || events.length === 0) return;

        try {
            await api.post('/analytics/track/batch', { events });
        } catch (e) {
            console.warn('Analytics batch tracking failed:', e);
        }
    };

    return {
        trackEvent,
        trackClick,
        trackDownload,
        trackFormSubmit,
        trackSearch,
        trackPageView,
        trackBatch,
    };
}
