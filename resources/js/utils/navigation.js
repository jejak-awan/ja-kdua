/**
 * Navigation groups configuration for admin sidebar
 * Reorganized for better UX and logical grouping
 */
export const navigationGroups = {
    // Content Management (4 items)
    content: [
        { name: 'contents', to: '/admin/contents', label: 'Contents' },
        { name: 'categories', to: '/admin/categories', label: 'Categories' },
        { name: 'tags', to: '/admin/tags', label: 'Tags' },
    ],

    // Media (2 items)
    media: [
        { name: 'media', to: '/admin/media', label: 'Media Library' },
        { name: 'file-manager', to: '/admin/file-manager', label: 'File Manager' },
    ],

    // Engagement (3 items)
    engagement: [
        { name: 'comments', to: '/admin/comments', label: 'Comments' },
        { name: 'forms', to: '/admin/forms', label: 'Forms' },
        { name: 'newsletter', to: '/admin/newsletter', label: 'Newsletter' },
    ],

    // Users & Access (3 items)
    users: [
        { name: 'users', to: '/admin/users', label: 'Users' },
        { name: 'roles', to: '/admin/roles', label: 'Roles & Permissions' },
        { name: 'activity-logs', to: '/admin/activity-logs', label: 'Activity Logs' },
    ],

    // Appearance (4 items)
    appearance: [
        { name: 'themes', to: '/admin/themes', label: 'Themes' },
        { name: 'menus', to: '/admin/menus', label: 'Menus' },
        { name: 'widgets', to: '/admin/widgets', label: 'Widgets' },
        { name: 'languages', to: '/admin/languages', label: 'Languages' },
    ],

    // Analytics & SEO (3 items)
    analytics: [
        { name: 'analytics', to: '/admin/analytics', label: 'Analytics' },
        { name: 'seo', to: '/admin/seo', label: 'SEO Tools' },
        { name: 'redirects', to: '/admin/redirects', label: 'Redirects' },
    ],

    // System (6 items)
    system: [
        { name: 'settings', to: '/admin/settings', label: 'Settings' },
        { name: 'backups', to: '/admin/backups', label: 'Backups' },
        { name: 'security', to: '/admin/security', label: 'Security' },
        { name: 'redis', to: '/admin/redis', label: 'Redis Cache' },
        { name: 'system', to: '/admin/system', label: 'System Info' },
        { name: 'scheduled-tasks', to: '/admin/scheduled-tasks', label: 'Scheduled Tasks' },
    ],

    // Developer (3 items)
    developer: [
        { name: 'webhooks', to: '/admin/webhooks', label: 'Webhooks' },
        { name: 'custom-fields', to: '/admin/custom-fields', label: 'Custom Fields' },
        { name: 'plugins', to: '/admin/plugins', label: 'Plugins' },
    ],
};



