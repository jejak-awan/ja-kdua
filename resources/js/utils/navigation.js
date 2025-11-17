/**
 * Navigation groups configuration for admin sidebar
 */
export const navigationGroups = {
    content: [
        { name: 'contents', to: '/admin/contents', label: 'Contents' },
        { name: 'media', to: '/admin/media', label: 'Media' },
        { name: 'categories', to: '/admin/categories', label: 'Categories' },
        { name: 'tags', to: '/admin/tags', label: 'Tags' },
    ],
    users: [
        { name: 'profile', to: '/admin/profile', label: 'My Profile' },
        { name: 'users', to: '/admin/users', label: 'Users' },
        { name: 'comments', to: '/admin/comments', label: 'Comments' },
        { name: 'forms', to: '/admin/forms', label: 'Forms' },
    ],
    analytics: [
        { name: 'analytics', to: '/admin/analytics', label: 'Analytics' },
        { name: 'seo', to: '/admin/seo', label: 'SEO Tools' },
        { name: 'redirects', to: '/admin/redirects', label: 'Redirects' },
    ],
    system: [
        { name: 'settings', to: '/admin/settings', label: 'Settings' },
        { name: 'security', to: '/admin/security', label: 'Security' },
        { name: 'system', to: '/admin/system', label: 'System Info' },
        { name: 'redis', to: '/admin/redis', label: 'Redis' },
        { name: 'backups', to: '/admin/backups', label: 'Backups' },
    ],
    monitoring: [
        { name: 'activity-logs', to: '/admin/activity-logs', label: 'Activity Logs' },
        { name: 'notifications', to: '/admin/notifications', label: 'Notifications' },
        { name: 'scheduled-tasks', to: '/admin/scheduled-tasks', label: 'Scheduled Tasks' },
        { name: 'logs', to: '/admin/logs', label: 'Log Viewer' },
    ],
    advanced: [
        { name: 'webhooks', to: '/admin/webhooks', label: 'Webhooks' },
        { name: 'custom-fields', to: '/admin/custom-fields', label: 'Custom Fields' },
        { name: 'file-manager', to: '/admin/file-manager', label: 'File Manager' },
        { name: 'themes', to: '/admin/themes', label: 'Themes' },
        { name: 'menus', to: '/admin/menus', label: 'Menus' },
        { name: 'widgets', to: '/admin/widgets', label: 'Widgets' },
        { name: 'plugins', to: '/admin/plugins', label: 'Plugins' },
        { name: 'languages', to: '/admin/languages', label: 'Languages' },
    ],
};

