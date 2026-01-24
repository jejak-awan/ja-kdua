import api from '../services/api';

/**
 * Analytics tracking composable for frontend event tracking
 */
export function useAnalytics() {
    /**
     * Track a custom event
     */
    const trackEvent = async (eventType: string, eventName: string, data: Record<string, any> = {}, contentId: number | null = null): Promise<void> => {
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
     */
    const trackClick = (buttonName: string, url: string | null = null) => {
        return trackEvent('click', `Click: ${buttonName}`, {
            button_name: buttonName,
            url: url || window.location.href,
        });
    };

    /**
     * Track a file download
     */
    const trackDownload = (fileName: string, fileType: string | null = null, mediaId: number | null = null) => {
        return trackEvent('download', `Download: ${fileName}`, {
            file_name: fileName,
            file_type: fileType,
            media_id: mediaId,
        });
    };

    /**
     * Track a form submission
     */
    const trackFormSubmit = (formName: string, data: Record<string, any> = {}) => {
        return trackEvent('form_submit', `Form Submit: ${formName}`, {
            form_name: formName,
            ...data,
        });
    };

    /**
     * Track a search query
     */
    const trackSearch = (query: string, resultsCount: number = 0) => {
        return trackEvent('search', `Search: ${query}`, {
            query: query,
            results_count: resultsCount,
        });
    };

    /**
     * Track page view (for SPA navigation)
     */
    const trackPageView = (pageName: string, url: string | null = null) => {
        return trackEvent('page_view', `Page View: ${pageName}`, {
            page_name: pageName,
            url: url || window.location.href,
        });
    };

    /**
     * Track multiple events in batch
     */
    const trackBatch = async (events: Array<{ type: string; name: string; data: any; content_id?: number }>): Promise<void> => {
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
