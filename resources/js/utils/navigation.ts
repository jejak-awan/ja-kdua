/**
 * Navigation groups configuration for admin sidebar
 * Reorganized for better UX and logical grouping
 */

export interface NavItem {
    name?: string;
    to?: string;
    label?: string;
    labelKey?: string;
    icon?: string;
    permission?: string;
    type?: 'item' | 'divider';
    children?: NavItem[];
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

    // K2NET ISP Management
    isp: [
        {
            label: 'Monitoring & Analytics',
            labelKey: 'common.navigation.sections.monitoring_analytics',
            icon: 'activity',
            children: [
                { name: 'isp-monitor', to: '/admin/isp/monitor', label: 'NOC Dashboard', icon: 'activity', permission: 'manage settings' },
                { name: 'isp.monitor.traffic', to: '/admin/isp/monitor/traffic', label: 'Real-time Traffic', icon: 'trending-up', permission: 'manage settings' },
                { name: 'isp-radius-sessions', to: '/admin/isp/monitoring/radius/sessions', label: 'Online Sessions (RADIUS)', icon: 'users', permission: 'manage settings' },
                { name: 'isp-radius-logs', to: '/admin/isp/monitoring/radius/logs', label: 'RADIUS Logs', icon: 'file-text', permission: 'manage settings' },
                { name: 'isp-active-sessions', to: '/admin/isp/monitoring/sessions', label: 'Router Sessions (API)', icon: 'network', permission: 'manage settings' },
            ]
        },
        {
            label: 'Configuration',
            labelKey: 'common.navigation.sections.configuration',
            icon: 'settings',
            children: [
                { name: 'isp-settings', to: '/admin/isp/settings', label: 'ISP Settings', icon: 'settings', permission: 'manage settings' },
            ]
        },
        {
            label: 'Customers',
            labelKey: 'common.navigation.sections.customers',
            icon: 'users',
            children: [
                { name: 'isp-subscription-customers', to: '/admin/isp/subscription/customers', label: 'Customer Management', icon: 'users', permission: 'manage settings' },
                { name: 'isp.customers.map', to: '/admin/isp/customers/map', label: 'Customer Map', icon: 'map', permission: 'manage settings' },
                { name: 'isp-subscription-profiles', to: '/admin/isp/subscription/profiles', label: 'Subscription Profiles', icon: 'package', permission: 'manage settings' },
                { name: 'isp-contracts', to: '/admin/isp/contracts', label: 'Service Contracts', icon: 'file-check', permission: 'manage settings' },
                { name: 'isp.admin.service-requests', to: '/admin/isp/admin/service-requests', label: 'Service Requests', icon: 'file-plus', permission: 'manage settings' },
            ]
        },
        {
            label: 'Network',
            labelKey: 'common.navigation.sections.network',
            icon: 'network',
            children: [
                { name: 'isp-infra', to: '/admin/isp/infra', label: 'Infrastructure', icon: 'server', permission: 'manage settings' },
                { name: 'isp.network.topology', to: '/admin/isp/network/topology', label: 'Network Topology', icon: 'network', permission: 'manage settings' },
                { name: 'isp-router', to: '/admin/isp/network/routers', label: 'Router Manager', icon: 'router', permission: 'manage settings' },
                { name: 'isp-network-ipam', to: '/admin/isp/network/ipam', label: 'IPAM Manager', icon: 'layers', permission: 'manage settings' },
                { name: 'isp-network-ip-pools', to: '/admin/isp/network/ip-pools', label: 'IP Pool', icon: 'database', permission: 'manage settings' },
                { name: 'isp-network-profiles', to: '/admin/isp/network/profiles', label: 'Network Profiles', icon: 'menu', permission: 'manage settings' },
                { name: 'isp-odp', to: '/admin/isp/odp', label: 'ODP Manager', icon: 'box', permission: 'manage settings' },
                { name: 'isp-outages', to: '/admin/isp/outages', label: 'Outage Tracker', icon: 'zap', permission: 'manage settings' },
            ]
        },
        {
            label: 'Billing',
            labelKey: 'common.navigation.sections.billing',
            icon: 'credit-card',
            children: [
                { name: 'isp-billing', to: '/admin/isp/billing', label: 'Billing', icon: 'credit-card', permission: 'manage settings' },
                { name: 'isp-payments', to: '/admin/isp/billing/payments', label: 'Payment History', icon: 'history', permission: 'manage settings' },
                { name: 'isp-payment-gateway', to: '/admin/isp/billing/gateway', label: 'Payment Gateway', icon: 'wallet', permission: 'manage settings' },
                { name: 'isp.admin.analytics.revenue', to: '/admin/isp/admin/analytics/revenue', label: 'Revenue Reports', icon: 'trending-up', permission: 'manage settings' },
            ]
        },
        {
            label: 'Inventory',
            labelKey: 'common.navigation.sections.inventory',
            icon: 'package',
            children: [
                { name: 'isp-inventory', to: '/admin/isp/inventory', label: 'Stock Items', icon: 'package', permission: 'manage settings' },
                { name: 'isp-vouchers', to: '/admin/isp/vouchers', label: 'Hotspot Voucher', icon: 'ticket', permission: 'manage settings' },
            ]
        },
        {
            label: 'Hotspot',
            labelKey: 'common.navigation.sections.hotspot',
            icon: 'wifi',
            children: [
                { name: 'isp.hotspot.ip-bindings', to: '/admin/isp/hotspot/ip-bindings', label: 'IP Binding', icon: 'link', permission: 'manage settings' },
                { name: 'isp.hotspot.cookies', to: '/admin/isp/hotspot/cookies', label: 'Hotspot Cookies', icon: 'cookie', permission: 'manage settings' },
            ]
        },
        {
            label: 'Reports',
            labelKey: 'common.navigation.sections.reports',
            icon: 'bar-chart-2',
            children: [
                { name: 'isp.reports.sales', to: '/admin/isp/reports/sales', label: 'Sales Reports', icon: 'pie-chart', permission: 'manage settings' },
            ]
        },
        {
            label: 'Support',
            labelKey: 'common.navigation.sections.support',
            icon: 'ticket',
            children: [
                { name: 'isp-support', to: '/admin/isp/support', label: 'Ticket Manager', icon: 'ticket', permission: 'manage settings' },
                { name: 'isp.support.wizard', to: '/admin/isp/support/wizard', label: 'Diagnostics Wizard', icon: 'wand', permission: 'manage settings' },
            ]
        },
        {
            label: 'Member Portal',
            labelKey: 'common.navigation.sections.member_portal',
            icon: 'user',
            children: [
                { name: 'isp-member', to: '/admin/isp/member', label: 'My Dashboard', icon: 'user', permission: 'view profile' },
                { name: 'isp-member-support', to: '/admin/isp/member/support', label: 'Contact Support', icon: 'message-square', permission: 'view profile' },
            ]
        },
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
