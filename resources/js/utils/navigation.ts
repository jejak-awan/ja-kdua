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

    // K2NET ISP Management — Restructured
    isp: [
        {
            label: 'Dashboard',
            labelKey: 'common.navigation.sections.dashboard',
            icon: 'layout',
            children: [
                { name: 'isp-admin', to: '/admin/isp/admin', label: 'Command Center', labelKey: 'common.navigation.menu.ispAdmin', icon: 'layout', permission: 'manage settings' },
                { name: 'isp-monitor', to: '/admin/isp/monitor', label: 'NOC Dashboard', labelKey: 'common.navigation.menu.ispMonitor', icon: 'monitor', permission: 'manage settings' },
                { name: 'isp-outages', to: '/admin/isp/outages', label: 'Outage Tracker', labelKey: 'common.navigation.menu.ispOutages', icon: 'zap', permission: 'manage settings' },
            ]
        },
        {
            label: 'Jaringan',
            labelKey: 'common.navigation.sections.network',
            icon: 'server',
            children: [
                { name: 'isp-infra', to: '/admin/isp/infra', label: 'Infrastruktur', labelKey: 'common.navigation.menu.ispInfra', icon: 'server', permission: 'manage settings' },
                { name: 'isp-olts', to: '/admin/isp/network/olts', label: 'Fiber Network (OLT)', labelKey: 'common.navigation.menu.ispOlts', icon: 'network', permission: 'manage settings' },
                { name: 'isp-odp', to: '/admin/isp/odp', label: 'ODP Management', labelKey: 'common.navigation.menu.ispOdp', icon: 'git-branch', permission: 'manage settings' },
                { name: 'isp-network-ipam', to: '/admin/isp/network/ipam', label: 'IP Management', labelKey: 'common.navigation.menu.ispNetworkIpam', icon: 'layers', permission: 'manage settings' },
                { name: 'isp-network-profiles', to: '/admin/isp/network/profiles', label: 'Profil Layanan', labelKey: 'common.navigation.menu.ispNetworkProfiles', icon: 'menu', permission: 'manage settings' },
            ]
        },
        {
            label: 'Pelanggan',
            labelKey: 'common.navigation.sections.customers',
            icon: 'users',
            children: [
                { name: 'isp-subscription-customers', to: '/admin/isp/subscription/customers', label: 'Daftar Pelanggan', labelKey: 'common.navigation.menu.ispClientDirectory', icon: 'users', permission: 'manage settings' },
                { name: 'isp-subscription-profiles', to: '/admin/isp/subscription/profiles', label: 'Paket Layanan', labelKey: 'common.navigation.menu.ispSubscriptionPlans', icon: 'package', permission: 'manage settings' },
                { name: 'isp-contracts', to: '/admin/isp/contracts', label: 'Kontrak', labelKey: 'common.navigation.menu.ispContracts', icon: 'file-check', permission: 'manage settings' },
                { name: 'isp.admin.service-requests', to: '/admin/isp/admin/service-requests', label: 'Permintaan Layanan', labelKey: 'common.navigation.menu.ispServiceRequests', icon: 'file-plus', permission: 'manage settings' },
                { name: 'isp-partners', to: '/admin/isp/partners', label: 'Mitra', labelKey: 'common.navigation.menu.ispPartners', icon: 'share-2', permission: 'manage settings' },
                { name: 'isp.customers.map', to: '/admin/isp/customers/map', label: 'Peta Pelanggan', labelKey: 'common.navigation.menu.ispCustomerMap', icon: 'map-pin', permission: 'manage settings' },
            ]
        },
        {
            label: 'Keuangan',
            labelKey: 'common.navigation.sections.billing',
            icon: 'credit-card',
            children: [
                { name: 'isp-billing', to: '/admin/isp/billing', label: 'Tagihan', labelKey: 'common.navigation.menu.ispBilling', icon: 'file-invoice', permission: 'manage settings' },
                { name: 'isp-payments', to: '/admin/isp/billing/payments', label: 'Riwayat Bayar', labelKey: 'common.navigation.menu.ispPaymentHistory', icon: 'history', permission: 'manage settings' },
                { name: 'isp-analytics', to: '/admin/isp/analytics', label: 'Analitik', labelKey: 'common.navigation.menu.ispAnalytics', icon: 'bar-chart', permission: 'manage settings' },
                { name: 'isp-coupons', to: '/admin/isp/coupons', label: 'Kupon & Promo', labelKey: 'common.navigation.menu.ispCoupons', icon: 'percent', permission: 'manage settings' },
            ]
        },
        {
            label: 'Hotspot & Inventaris',
            labelKey: 'common.navigation.sections.inventory',
            icon: 'archive',
            children: [
                { name: 'isp-inventory', to: '/admin/isp/inventory', label: 'Stok Barang', labelKey: 'common.navigation.menu.ispInventory', icon: 'archive', permission: 'manage settings' },
                { name: 'isp-hotspot', to: '/admin/isp/hotspot', label: 'Hotspot & Voucher', labelKey: 'common.navigation.menu.ispHotspotVouchers', icon: 'ticket', permission: 'manage settings' },
            ]
        },
        {
            label: 'Pengaturan',
            labelKey: 'common.navigation.sections.isp_admin',
            icon: 'settings',
            children: [
                { name: 'isp-support', to: '/admin/isp/support', label: 'Tiket Bantuan', labelKey: 'common.navigation.menu.ispSupport', icon: 'life-buoy', permission: 'manage settings' },
                { name: 'isp-whatsapp', to: '/admin/isp/whatsapp', label: 'WA Blast', labelKey: 'common.navigation.menu.ispWaBlast', icon: 'message-circle', permission: 'manage settings' },
                { name: 'isp-bgp-toolkit', to: '/admin/isp/tools/bgp', label: 'BGP Toolkit', labelKey: 'common.navigation.menu.ispBgpToolkit', icon: 'globe', permission: 'manage settings' },
                { name: 'isp.support.wizard', to: '/admin/isp/support/wizard', label: 'Diagnosis', labelKey: 'common.navigation.menu.ispDiagnostics', icon: 'wand', permission: 'manage settings' },
                { name: 'isp-deployments', to: '/admin/isp/operations/deployments', label: 'Dispatch Center', labelKey: 'common.navigation.menu.ispDeployments', icon: 'truck', permission: 'manage settings' },
                { name: 'isp-settings', to: '/admin/isp/settings', label: 'Pengaturan ISP', labelKey: 'common.navigation.menu.settings', icon: 'settings', permission: 'manage settings' },
                { name: 'isp-activity-logs', to: '/admin/isp/admin/activity-logs', label: 'Log Aktivitas', labelKey: 'common.navigation.menu.ispAuditLogs', icon: 'scroll', permission: 'manage settings' },
            ]
        },
        {
            label: 'Member Portal',
            labelKey: 'common.navigation.sections.member_portal',
            icon: 'user',
            children: [
                { name: 'isp-member', to: '/admin/isp/member', label: 'My Dashboard', labelKey: 'common.navigation.menu.ispMemberPortal', icon: 'home', permission: 'view profile' },
                { name: 'isp-member-support', to: '/admin/isp/member/support', label: 'Contact Support', labelKey: 'common.navigation.menu.ispMemberSupport', icon: 'help-circle', permission: 'view profile' },
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
