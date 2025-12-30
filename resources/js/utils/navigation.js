/**
 * Navigation groups configuration for admin sidebar
 * Reorganized for better UX and logical grouping
 */
export const navigationGroups = {
    // Content Management (4 items)
    content: [
        { name: 'contents', to: '/admin/contents', label: 'Contents', permission: 'manage content' },
        { name: 'categories', to: '/admin/categories', label: 'Categories', permission: 'manage categories' },
        { name: 'tags', to: '/admin/tags', label: 'Tags', permission: 'manage tags' },
    ],

    // Media (2 items)
    media: [
        { name: 'media', to: '/admin/media', label: 'Media Library', permission: 'manage media' },
        { name: 'file-manager', to: '/admin/file-manager', label: 'File Manager', permission: 'manage files' },
    ],

    // Engagement (3 items)
    engagement: [
        { name: 'comments', to: '/admin/comments', label: 'Comments', permission: 'manage comments' },
        { name: 'forms', to: '/admin/forms', label: 'Forms', permission: 'manage forms' },
        { name: 'newsletter', to: '/admin/newsletter', label: 'Newsletter', permission: 'manage users' },
    ],

    // Users & Access (2 items)
    users: [
        { name: 'users', to: '/admin/users', label: 'Users', permission: 'manage users' },
        { name: 'roles', to: '/admin/roles', label: 'Roles & Permissions', permission: 'manage roles' },
    ],

    // Logs & Monitoring (4 items)
    logs: [
        { name: 'logs-dashboard', to: '/admin/logs-dashboard', label: 'Logs Dashboard', permission: 'view logs' },
        { name: 'activity-logs', to: '/admin/activity-logs', label: 'Activity Logs', permission: 'view activity logs' },
        { name: 'login-history', to: '/admin/login-history', label: 'Login History', permission: 'manage users' },
        { name: 'security', to: '/admin/security', label: 'Security Logs', permission: 'view security logs' },
    ],

    // Appearance (4 items)
    appearance: [
        { name: 'themes', to: '/admin/themes', label: 'Themes', permission: 'manage themes' },
        { name: 'menus', to: '/admin/menus', label: 'Menus', permission: 'manage menus' },
        { name: 'widgets', to: '/admin/widgets', label: 'Widgets', permission: 'manage widgets' },
        { name: 'languages', to: '/admin/languages', label: 'Languages', permission: 'manage settings' },
    ],

    // Analytics & SEO (3 items)
    analytics: [
        { name: 'analytics', to: '/admin/analytics', label: 'Analytics', permission: 'view analytics' },
        { name: 'seo', to: '/admin/seo', label: 'SEO Tools', permission: 'manage settings' },
        { name: 'redirects', to: '/admin/redirects', label: 'Redirects', permission: 'manage settings' },
    ],

    // System (6 items) - Info Sistem at top as system dashboard
    system: [
        { name: 'system', to: '/admin/system', label: 'System Info', permission: 'manage system' },
        { name: 'settings', to: '/admin/settings', label: 'Settings', permission: 'manage settings' },
        { name: 'backups', to: '/admin/backups', label: 'Backups', permission: 'manage backups' },
        { name: 'redis', to: '/admin/redis', label: 'Redis Cache', permission: 'manage settings' },
        { name: 'scheduled-tasks', to: '/admin/scheduled-tasks', label: 'Scheduled Tasks', permission: 'manage scheduled tasks' },
        { name: 'command-runner', to: '/admin/command-runner', label: 'Command Runner', permission: 'manage system' },
    ],

    // Developer (3 items)
    developer: [
        { name: 'webhooks', to: '/admin/webhooks', label: 'Webhooks', permission: 'manage settings' },
        { name: 'custom-fields', to: '/admin/custom-fields', label: 'Custom Fields', permission: 'manage content' },
        { name: 'plugins', to: '/admin/plugins', label: 'Plugins', permission: 'manage plugins' },
    ],
};
