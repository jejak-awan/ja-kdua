/**
 * Navigation groups configuration for admin sidebar
 * Reorganized for better UX and logical grouping
 */

export interface NavItem {
    name?: string;
    to?: string;
    label?: string;
    permission?: string;
    type?: 'item' | 'divider';
}

export const navigationGroups: Record<string, NavItem[]> = {
    // Content Management (5 items)
    content: [
        { name: 'studio', to: '/admin/studio', label: 'Contents', permission: 'view content' },
        { name: 'media', to: '/admin/media', label: 'Media Library', permission: 'view media' },
        { name: 'file-manager', to: '/admin/file-manager', label: 'File Manager', permission: 'view files' },
        { name: 'comments', to: '/admin/comments', label: 'Comments', permission: 'view comments' },
        { name: 'custom-fields', to: '/admin/custom-fields', label: 'Custom Fields', permission: 'manage content' },
    ],

    // Marketing & SEO (5 items)
    marketing: [
        { name: 'forms', to: '/admin/forms', label: 'Forms', permission: 'view forms' },
        { name: 'newsletter', to: '/admin/newsletter', label: 'Newsletter', permission: 'view newsletter' },
        { name: 'email-templates', to: '/admin/email-templates', label: 'Email Templates', permission: 'manage settings' },
        { name: 'seo', to: '/admin/seo', label: 'SEO Tools', permission: 'manage settings' },
        { name: 'redirects', to: '/admin/redirects', label: 'Redirects', permission: 'view redirects' },
    ],

    // Users & Access (2 items)
    users: [
        { name: 'users', to: '/admin/users', label: 'Users', permission: 'view users' },
        { name: 'roles', to: '/admin/roles', label: 'Roles & Permissions', permission: 'view roles' },
    ],

    // Logs & Monitoring (6 items)
    logs: [
        { name: 'journal-dashboard', to: '/admin/journal-dashboard', label: 'Journal Dashboard', permission: 'view logs' },
        { name: 'activity-journal', to: '/admin/activity-journal', label: 'Activity Journal', permission: 'view activity logs' },
        { name: 'access-journal', to: '/admin/access-journal', label: 'Access History', permission: 'view users' },
        { name: 'security-journal', to: '/admin/security-journal', label: 'Security Journal', permission: 'view security logs' },
        { type: 'divider', label: 'monitoring' },
        { name: 'system-journal', to: '/admin/system-journal', label: 'System Journal', permission: 'view system' },
        { name: 'analytics', to: '/admin/analytics', label: 'Analytics', permission: 'view analytics' },
    ],

    // Appearance (3 items)
    appearance: [
        { name: 'builder.site', to: '/admin/site-editor', label: 'Site Editor', permission: 'manage settings' },
        { name: 'themes', to: '/admin/themes', label: 'Themes', permission: 'view themes' },
        { name: 'menus', to: '/admin/menus', label: 'Menus', permission: 'view menus' },
        { name: 'widgets', to: '/admin/widgets', label: 'Widgets', permission: 'view widgets' },
        { name: 'languages', to: '/admin/languages', label: 'Languages', permission: 'view settings' },
    ],

    // System (7 items) - Info Sistem at top as system dashboard
    system: [
        { name: 'system', to: '/admin/system', label: 'System Info', permission: 'view system' },
        { name: 'settings', to: '/admin/settings', label: 'Settings', permission: 'view settings' },
        { name: 'system-notifications', to: '/admin/system/notifications', label: 'Notifications', permission: 'manage system' },
        { name: 'backups', to: '/admin/backups', label: 'Backups', permission: 'view backups' },
        { name: 'redis', to: '/admin/redis', label: 'Redis Cache', permission: 'manage settings' },
        { name: 'scheduled-tasks', to: '/admin/scheduled-tasks', label: 'Scheduled Tasks', permission: 'view scheduled tasks' },
    ],

    // Developer (2 items)
    developer: [
        { name: 'webhooks', to: '/admin/webhooks', label: 'Webhooks', permission: 'manage settings' },
        { name: 'plugins', to: '/admin/plugins', label: 'Plugins', permission: 'view plugins' },
    ],
};
