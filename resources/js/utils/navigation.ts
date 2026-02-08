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

    // K2NET ISP Management
    isp: [
        // Monitoring & Analytics
        { type: 'divider', label: 'Monitoring & Analitik' },
        { name: 'isp-monitor', to: '/admin/isp/monitor', label: 'Dasbor NOC', permission: 'manage settings' },
        { name: 'isp.monitor.traffic', to: '/admin/isp/monitor/traffic', label: 'Trafik Real-time', permission: 'manage settings' },
        { name: 'isp-active-sessions', to: '/admin/isp/monitoring/sessions', label: 'Sesi Online', permission: 'manage settings' },
        { name: 'isp.admin.analytics.usage', to: '/admin/isp/admin/analytics/usage', label: 'Penggunaan Jaringan', permission: 'manage settings' },
        { name: 'isp.admin.analytics.revenue', to: '/admin/isp/admin/analytics/revenue', label: 'Laporan Pendapatan', permission: 'manage settings' },

        // Configuration
        { type: 'divider', label: 'Konfigurasi' },
        { name: 'isp-settings', to: '/admin/isp/settings', label: 'Pengaturan ISP', permission: 'manage settings' },

        // Customers
        { type: 'divider', label: 'Pelanggan' },
        { name: 'isp-subscription-customers', to: '/admin/isp/subscription/customers', label: 'Manajemen Pelanggan', permission: 'manage settings' },
        { name: 'isp.customers.map', to: '/admin/isp/customers/map', label: 'Peta Pelanggan', permission: 'manage settings' },
        { name: 'isp-subscription-profiles', to: '/admin/isp/subscription/profiles', label: 'Profil Langganan', permission: 'manage settings' },
        { name: 'isp.admin.service-requests', to: '/admin/isp/admin/service-requests', label: 'Pengajuan Layanan', permission: 'manage settings' },

        // Network & Infrastructure
        { type: 'divider', label: 'Jaringan' },
        { name: 'isp-infra', to: '/admin/isp/infra', label: 'Infrastruktur', permission: 'manage settings' },
        { name: 'isp.network.topology', to: '/admin/isp/network/topology', label: 'Topologi Jaringan', permission: 'manage settings' },
        { name: 'isp-router', to: '/admin/isp/network/routers', label: 'Manajer Router', permission: 'manage settings' },
        { name: 'isp-network-ipam', to: '/admin/isp/network/ipam', label: 'Manajer IPAM', permission: 'manage settings' },
        { name: 'isp-network-profiles', to: '/admin/isp/network/profiles', label: 'Profil Jaringan', permission: 'manage settings' },
        { name: 'isp-odp', to: '/admin/isp/odp', label: 'Manajer ODP', permission: 'manage settings' },

        // Billing
        { type: 'divider', label: 'Penagihan' },
        { name: 'isp-billing', to: '/admin/isp/billing', label: 'Penagihan', permission: 'manage settings' },

        // Inventory & Operations
        { type: 'divider', label: 'Inventaris & Operasional' },
        { name: 'isp-inventory', to: '/admin/isp/inventory', label: 'Stok Barang', permission: 'manage settings' },
        { name: 'isp-vouchers', to: '/admin/isp/vouchers', label: 'Voucher Hotspot', permission: 'manage settings' },
        { name: 'isp-outages', to: '/admin/isp/outages', label: 'Pelacak Gangguan', permission: 'manage settings' },

        // Support
        { type: 'divider', label: 'Dukungan' },
        { name: 'isp-support', to: '/admin/isp/support', label: 'Manajer Tiket', permission: 'manage settings' },
        { name: 'isp.support.wizard', to: '/admin/isp/support/wizard', label: 'Wizard Diagnosa', permission: 'manage settings' },

        // Member Portal Section
        { type: 'divider', label: 'Member Portal' },
        { name: 'isp-member', to: '/admin/isp/member', label: 'Dasbor Saya', permission: 'view profile' },
        { name: 'isp-member-support', to: '/admin/isp/member/support', label: 'Hubungi Dukungan', permission: 'view profile' },
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
