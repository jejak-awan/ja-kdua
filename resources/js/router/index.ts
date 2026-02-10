import { logger } from '@/utils/logger';
import type { RouteRecordRaw } from 'vue-router';
import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';
import frontendRoutes from './frontend';
import { useSystemError } from '../composables/useSystemError';

// Type augmentation for RouteMeta
declare module 'vue-router' {
    interface RouteMeta {
        requiresAuth?: boolean;
        public?: boolean;
        guest?: boolean;
        permission?: string;
        requiresSuperAdmin?: boolean;
        title?: string;
        layout?: string;
    }
}

const routes: Array<RouteRecordRaw> = [
    // Public ISP Status
    {
        path: '/status',
        name: 'isp.status',
        component: () => import('../views/Isp/Public/Status.vue'),
        meta: { public: true, layout: 'empty' },
    },
    // Frontend routes (public)
    ...frontendRoutes,

    // Auth routes
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/auth/Login.vue'),
        meta: { guest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('../views/auth/Register.vue'),
        meta: { guest: true },
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('../views/auth/ForgotPassword.vue'),
        meta: { guest: true },
    },
    {
        path: '/reset-password',
        name: 'reset-password',
        component: () => import('../views/auth/ResetPassword.vue'),
        meta: { guest: true },
    },
    {
        path: '/verify-email',
        name: 'verify-email',
        component: () => import('../views/auth/VerifyEmail.vue'),
        meta: { guest: true },
    },
    {
        path: '/admin',
        component: () => import('../layouts/AdminLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('../views/admin/Dashboard.vue'),
            },
            {
                path: 'studio',
                name: 'studio',
                component: () => import('../views/admin/studio/Index.vue'),
                meta: { permission: 'manage content' },
            },
            {
                path: 'contents',
                name: 'contents',
                component: () => import('../views/admin/studio/contents/Index.vue'),
                meta: { permission: 'manage content' },
            },
            {
                path: 'contents/calendar',
                name: 'contents.calendar',
                component: () => import('../views/admin/studio/contents/Calendar.vue'),
            },
            {
                path: 'content-templates/create',
                name: 'content-templates.create',
                component: () => import('../views/admin/studio/templates/Create.vue'),
                meta: { permission: 'create content templates' },
            },
            {
                path: 'content-templates/:id/edit',
                name: 'content-templates.edit',
                component: () => import('../views/admin/studio/templates/Edit.vue'),
                meta: { permission: 'edit content templates' },
            },
            {
                path: 'contents/create',
                name: 'contents.create',
                component: () => import('../views/admin/studio/contents/Create.vue'),
            },
            {
                path: 'contents/:id/edit',
                name: 'contents.edit',
                component: () => import('../views/admin/studio/contents/Edit.vue'),
            },
            {
                path: 'contents/:id/revisions',
                name: 'contents.revisions',
                component: () => import('../views/admin/studio/contents/Revisions.vue'),
            },
            {
                path: 'site-editor',
                name: 'builder.site',
                component: () => import('../views/admin/style/builder/SiteEditor.vue'),
                meta: { permission: 'manage settings' },
            },
            {
                path: 'media',
                name: 'media',
                component: () => import('../views/admin/studio/media/Index.vue'),
                meta: { permission: 'manage media' },
            },
            {
                path: 'categories',
                name: 'categories',
                component: () => import('../views/admin/studio/categories/Index.vue'),
            },
            {
                path: 'users',
                name: 'users.index',
                component: () => import('../views/admin/team/users/Index.vue'),
                meta: { permission: 'manage users' },
            },
            {
                path: 'users/create',
                name: 'users.create',
                component: () => import('../views/admin/team/users/Create.vue'),
            },
            {
                path: 'users/:id/edit',
                name: 'users.edit',
                component: () => import('../views/admin/team/users/Edit.vue'),
            },
            {
                path: 'roles',
                name: 'roles',
                component: () => import('../views/admin/team/roles/Index.vue'),
                meta: { permission: 'view roles' },
            },
            {
                path: 'roles/create',
                name: 'roles.create',
                component: () => import('../views/admin/team/roles/Index.vue'),
            },
            {
                path: 'roles/:id/edit',
                name: 'roles.edit',
                component: () => import('../views/admin/team/roles/Index.vue'),
            },
            {
                path: 'settings',
                name: 'settings',
                component: () => import('../views/admin/settings/general/Index.vue'),
                meta: { permission: 'manage settings' },
            },
            {
                path: 'newsletter',
                name: 'newsletter',
                component: () => import('../views/admin/reach/newsletter/Index.vue'),
            },
            {
                path: 'isp/infra',
                name: 'isp-infra',
                component: () => import('../views/Isp/NetOps/Infrastructure/Index.vue'),
            },
            {
                path: 'isp/settings',
                name: 'isp-settings',
                component: () => import('../views/Isp/Support/Settings/Index.vue'),
                meta: { permission: 'manage settings' },
            },
            {
                path: 'isp/admin/service-requests',
                name: 'isp.admin.service-requests',
                component: () => import('../views/Isp/Business/Customer/ServiceRequests.vue'),
            },
            {
                path: 'isp/admin/analytics/usage',
                name: 'isp.admin.analytics.usage',
                component: () => import('../views/Isp/Monitor/UsageDashboard.vue'),
            },
            {
                path: 'isp/admin/analytics/revenue',
                name: 'isp.admin.analytics.revenue',
                component: () => import('../views/Isp/Business/Billing/Reports/Revenue.vue'),
            },
            {
                path: 'isp/odp',
                name: 'isp-odp',
                component: () => import('../views/Isp/NetOps/Odp/Index.vue'),
            },
            {
                path: 'isp/inventory',
                name: 'isp-inventory',
                component: () => import('../views/Isp/Business/Inventory/Stock.vue'),
            },
            {
                path: 'isp/vouchers',
                name: 'isp-vouchers',
                component: () => import('../views/Isp/Business/Inventory/Vouchers.vue'),
            },
            {
                path: 'isp/vouchers/sold',
                name: 'isp-vouchers-sold',
                component: () => import('../views/Isp/Business/Inventory/Sold.vue'),
            },
            {
                path: 'isp/vouchers/print/:batchId',
                name: 'isp.vouchers.print',
                component: () => import('../views/Isp/Business/Inventory/BatchPrint.vue'),
                meta: { layout: 'empty' }
            },
            {
                path: 'isp/partners',
                name: 'isp-partners',
                component: () => import('../views/Isp/Business/Partner/Index.vue'),
            },
            {
                path: 'isp/coupons',
                name: 'isp-coupons',
                component: () => import('../views/Isp/Business/Billing/Coupons.vue'),
            },
            {
                path: 'isp/whatsapp',
                name: 'isp-whatsapp',
                component: () => import('../views/Isp/Support/WhatsApp/Index.vue'),
            },
            {
                path: 'isp/print-templates',
                name: 'isp-print-templates',
                component: () => import('../views/Isp/Support/Settings/PrintTemplates.vue'),
            },
            {
                path: 'isp/tools/bgp',
                name: 'isp-bgp-toolkit',
                component: () => import('../views/Isp/Support/Tools/BgpToolkit.vue'),
            },
            {
                path: 'isp/admin/activity-logs',
                name: 'isp-activity-logs',
                component: () => import('../views/Isp/Admin/ActivityLogs.vue'),
            },
            {
                path: 'isp/reports/sales',
                name: 'isp.reports.sales',
                component: () => import('../views/Isp/Reports/SalesReport.vue'),
            },
            {
                path: 'isp/hotspot/ip-bindings',
                name: 'isp.hotspot.ip-bindings',
                component: () => import('../views/Isp/Hotspot/IpBindings.vue'),
            },
            {
                path: 'isp/hotspot/cookies',
                name: 'isp.hotspot.cookies',
                component: () => import('../views/Isp/Hotspot/Cookies.vue'),
            },
            {
                path: 'isp/admin/monitoring',
                name: 'isp.infra.map',
                component: () => import('../views/Isp/NetOps/Infrastructure/Map.vue'),
            },
            {
                path: 'isp/network/topology',
                name: 'isp.network.topology',
                component: () => import('../views/Isp/NetOps/Topology/Index.vue'),
            },
            {
                path: 'isp/monitor',
                name: 'isp-monitor',
                component: () => import('../views/Isp/Monitor/NocDashboard.vue'),
            },
            {
                path: 'isp/monitor/traffic',
                name: 'isp.monitor.traffic',
                component: () => import('../views/Isp/Monitor/TrafficMonitor.vue'),
            },
            {
                path: 'isp/monitoring/sessions',
                name: 'isp-active-sessions',
                component: () => import('../views/Isp/Monitor/ActiveSessions.vue'),
                meta: { permission: 'manage settings' }
            },
            {
                path: 'isp/monitoring/radius/sessions',
                name: 'isp-radius-sessions',
                component: () => import('../views/Isp/Monitor/RadiusSessions.vue'),
                meta: { permission: 'manage settings' }
            },
            {
                path: 'isp/monitoring/radius/logs',
                name: 'isp-radius-logs',
                component: () => import('../views/Isp/Monitor/RadiusLogs.vue'),
                meta: { permission: 'manage settings' }
            },
            {
                path: 'isp/network/ip-pools',
                name: 'isp-network-ip-pools',
                component: () => import('../views/Isp/NetOps/IpPool/Index.vue'),
                meta: { permission: 'manage settings' }
            },
            {
                path: 'isp/network/ip-pools/create',
                name: 'isp-network-ip-pools-create',
                component: () => import('../views/Isp/NetOps/IpPool/Form.vue'),
                meta: { permission: 'manage settings' }
            },
            {
                path: 'isp/network/ip-pools/:id',
                name: 'isp-network-ip-pools-detail',
                component: () => import('../views/Isp/NetOps/IpPool/Detail.vue'),
                meta: { permission: 'manage settings' }
            },
            {
                path: 'isp/network/ip-pools/:id/edit',
                name: 'isp-network-ip-pools-edit',
                component: () => import('../views/Isp/NetOps/IpPool/Form.vue'),
                meta: { permission: 'manage settings' }
            },
            {
                path: 'isp/billing',
                name: 'isp-billing',
                component: () => import('../views/Isp/Business/Billing/Index.vue'),
            },
            {
                path: 'isp/billing/payments',
                name: 'isp-payments',
                component: () => import('../views/Isp/Business/Billing/Payments/Index.vue'),
            },
            {
                path: 'isp/billing/gateway',
                name: 'isp-payment-gateway',
                component: () => import('../views/Isp/Business/Billing/Payments/Gateway.vue'),
            },
            {
                path: 'isp/admin',
                name: 'isp-admin',
                component: () => import('../views/Isp/Administration/Index.vue'),
            },
            {
                path: 'isp/subscription/profiles',
                name: 'isp-subscription-profiles',
                component: () => import('../views/Isp/Business/Customer/Profiles.vue'),
            },
            {
                path: 'isp/subscription/customers',
                name: 'isp-subscription-customers',
                component: () => import('../views/Isp/Business/Customer/Index.vue'),
            },
            {
                path: 'isp/contracts',
                name: 'isp-contracts',
                component: () => import('../views/Isp/Business/Customer/Contracts/Index.vue'),
            },
            {
                path: 'isp/customers/map',
                name: 'isp.customers.map',
                component: () => import('../views/Isp/Business/Customer/Map.vue'),
            },
            {
                path: 'isp/member',
                name: 'isp-member',
                component: () => import('../views/Isp/Business/Portal/Dashboard.vue'),
            },
            {
                path: 'isp/member/invoices',
                name: 'isp.member.invoices',
                component: () => import('../views/Isp/Business/Portal/Invoices.vue'),
            },
            {
                path: 'isp/member/usage',
                name: 'isp.member.usage',
                component: () => import('../views/Isp/Business/Portal/UsageAnalytics.vue'),
            },
            {
                path: 'isp/member/support',
                name: 'isp-member-support',
                component: () => import('../views/Isp/Business/Portal/Support.vue'),
            },
            {
                path: 'isp/member/diagnostics',
                name: 'isp.member.diagnostics',
                component: () => import('../views/Isp/Business/Portal/Diagnostics.vue'),
            },
            {
                path: 'isp/support',
                name: 'isp-support',
                component: () => import('../views/Isp/Support/Index.vue'),
            },
            {
                path: 'isp/support/wizard',
                name: 'isp.support.wizard',
                component: () => import('../views/Isp/Support/TroubleshootWizard.vue'),
            },
            {
                path: 'isp/network/ipam',
                name: 'isp-network-ipam',
                component: () => import('../views/Isp/NetOps/Ipam/Index.vue'),
            },
            {
                path: 'isp/network/profiles',
                name: 'isp-network-profiles',
                component: () => import('../views/Isp/NetOps/ServiceProfiles/Index.vue'),
            },
            {
                path: 'isp/network/routers',
                name: 'isp-router',
                component: () => import('../views/Isp/NetOps/Routers/Index.vue'),
            },
            {
                path: 'isp/outages',
                name: 'isp-outages',
                component: () => import('../views/Isp/NetOps/Outages/Index.vue'),
            },
            {
                path: 'profile',
                name: 'profile',
                component: () => import('../views/admin/Profile.vue'),
            },
            {
                path: 'analytics',
                name: 'analytics',
                component: () => import('../views/admin/pulse/analytics/Index.vue'),
                meta: { permission: 'view analytics' },
            },
            {
                path: 'comments',
                name: 'comments',
                component: () => import('../views/admin/studio/comments/Index.vue'),
            },
            {
                path: 'forms',
                name: 'forms',
                component: () => import('../views/admin/reach/forms/Index.vue'),
            },
            {
                path: 'forms/create',
                name: 'forms.create',
                component: () => import('../views/admin/reach/forms/Create.vue'),
            },
            {
                path: 'forms/:id/edit',
                name: 'forms.edit',
                component: () => import('../views/admin/reach/forms/Edit.vue'),
            },
            {
                path: 'forms/:id/submissions',
                name: 'forms.submissions',
                component: () => import('../views/admin/reach/forms/SubmissionsPage.vue'),
            },
            {
                path: 'forms/:id/analytics',
                name: 'forms.analytics',
                component: () => import('../views/admin/reach/forms/AnalyticsPage.vue'),
            },
            {
                path: 'tags',
                name: 'tags',
                component: () => import('../views/admin/studio/tags/Index.vue'),
            },
            {
                path: 'email-templates',
                name: 'email-templates',
                component: () => import('../views/admin/reach/email-templates/Index.vue'),
            },
            {
                path: 'email-templates/create',
                name: 'email-templates.create',
                component: () => import('../views/admin/reach/email-templates/Create.vue'),
            },
            {
                path: 'email-templates/:id/edit',
                name: 'email-templates.edit',
                component: () => import('../views/admin/reach/email-templates/Edit.vue'),
            },

            {
                path: 'seo',
                name: 'seo',
                component: () => import('../views/admin/reach/seo/Index.vue'),
            },
            {
                path: 'cache',
                name: 'cache',
                component: () => import('../views/admin/settings/cache/Index.vue'),
            },
            {
                path: 'redirects',
                name: 'redirects',
                component: () => import('../views/admin/reach/redirects/Index.vue'),
            },
            {
                path: 'backups',
                name: 'backups',
                component: () => import('../views/admin/settings/backups/Index.vue'),
                meta: { permission: 'manage backups' },
            },
            {
                path: 'security-journal',
                name: 'security-journal',
                component: () => import('../views/admin/pulse/security/Index.vue'),
            },
            {
                path: 'system',
                name: 'system',
                component: () => import('../views/admin/settings/system/Index.vue'),
                meta: { permission: 'manage system' },
            },
            {
                path: 'redis',
                name: 'redis',
                component: () => import('../views/admin/settings/system/Redis.vue'),
                meta: { title: 'Redis Management', permission: 'manage settings' },
            },
            {
                path: 'system/notifications',
                name: 'system-notifications',
                component: () => import('../views/admin/settings/system/NotificationManager.vue'),
                meta: { title: 'Notification Manager', permission: 'manage system' },
            },
            {
                path: 'activity-journal',
                name: 'activity-journal',
                component: () => import('../views/admin/pulse/activity/Index.vue'),
            },
            {
                path: 'access-journal',
                name: 'access-journal',
                component: () => import('../views/admin/pulse/access/Index.vue'),
            },
            {
                path: 'journal-dashboard',
                name: 'journal-dashboard',
                component: () => import('../views/admin/pulse/Index.vue'),
            },
            {
                path: 'notifications',
                name: 'notifications',
                component: () => import('../views/admin/settings/notifications/Index.vue'),
            },
            {
                path: 'scheduled-tasks',
                name: 'scheduled-tasks',
                component: () => import('../views/admin/settings/system/ScheduledTasks.vue'),
                meta: { permission: 'manage scheduled tasks' },
            },

            {
                path: 'system-journal',
                name: 'system-journal',
                component: () => import('../views/admin/pulse/system/Index.vue'),
            },
            {
                path: 'webhooks',
                name: 'webhooks',
                component: () => import('../views/admin/dev/webhooks/Index.vue'),
            },
            {
                path: 'custom-fields',
                name: 'custom-fields',
                component: () => import('../views/admin/studio/custom-fields/Index.vue'),
            },
            {
                path: 'file-manager',
                name: 'file-manager',
                component: () => import('../views/admin/studio/file-manager/Index.vue'),
            },
            {
                path: 'search',
                name: 'search',
                component: () => import('../views/admin/studio/search/Index.vue'),
            },
            {
                path: 'themes',
                name: 'themes',
                component: () => import('../views/admin/style/themes/Index.vue'),
                meta: { permission: 'manage themes' },
            },


            {
                path: 'menus',
                name: 'menus',
                component: () => import('../views/admin/style/menus/Index.vue'),
                meta: { permission: 'manage menus' },
            },

            {
                path: 'widgets',
                name: 'widgets',
                component: () => import('../views/admin/style/widgets/Index.vue'),
                meta: { permission: 'manage widgets' },
            },
            {
                path: 'plugins',
                name: 'plugins',
                component: () => import('../views/admin/dev/plugins/Index.vue'),
                meta: { permission: 'manage plugins' },
            },
            {
                path: 'languages',
                name: 'languages',
                component: () => import('../views/admin/style/languages/Index.vue'),
                meta: { permission: 'manage settings' },
            },

        ],
    },




    // Error pages
    {
        path: '/403',
        name: 'forbidden',
        component: () => import('../views/errors/Forbidden.vue'),
        meta: { public: true },
    },
    {
        path: '/500',
        name: 'server-error',
        component: () => import('../views/errors/ServerError.vue'),
        meta: { public: true },
    },
    {
        path: '/404',
        name: 'not-found',
        component: () => import('../views/errors/NotFound.vue'),
        meta: { public: true },
    },
    {
        path: '/419',
        name: 'session-expired',
        component: () => import('../views/errors/SessionExpired.vue'),
        meta: { public: true },
    },
    {
        path: '/429',
        name: 'too-many-requests',
        component: () => import('../views/errors/RateLimit.vue'),
        meta: { public: true },
    },

    // Catch-all route (must be last)
    {
        path: '/:pathMatch(.*)*',
        name: 'catch-all',
        component: () => import('../views/errors/NotFound.vue'),
        meta: { public: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guard
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    // Public routes (home, error pages, etc.) - skip auth check
    if (to.meta.public) {
        next();
        return;
    }

    // Initialize auth for protected routes
    authStore.initAuth();

    // Check if any matched route requires auth (handles parent-child meta inheritance)
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    if (requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login', query: { redirect: to.fullPath } });
        return;
    }

    // If route is for guests but user is authenticated, redirect to dashboard
    if (to.meta.guest && authStore.isAuthenticated) {
        next({ name: 'dashboard' });
        return;
    }

    // Check for specific permissions
    if (to.meta.permission) {
        if (!authStore.hasPermission(to.meta.permission as string)) {
            next({ name: 'forbidden' });
            return;
        }
    }

    // Check for super admin requirement
    if (to.meta.requiresSuperAdmin && !authStore.isAtLeastRole('super-admin')) {
        next({ name: 'forbidden' });
        return;
    }

    // Allow navigation
    next();
});

// Global error handler
router.onError((error) => {
    logger.error('Router error:', error);

    // For runtime router errors, use the modal to avoid full page fallback if possible
    const { showError } = useSystemError();
    showError({
        code: 500,
        title: 'Application Error',
        message: error.message || 'A critical error occurred while navigating.',
        description: 'The application encountered an unexpected error. Please try refreshing or contact support if the issue persists.',
        reason: 'Router Navigation Error',
        redirect: '/' // Fallback to home
    });
});

// Analytics tracking
router.afterEach((to) => {
    // Skip tracking for admin routes
    if (to.path.startsWith('/admin')) {
        return;
    }

    // Track visit using static import
    api.post('/analytics/track-visit', {
        url: window.location.href,
        path: to.path,
        title: document.title
    }).catch(err => {
        // Silently fail for analytics errors
        logger.debug('Analytics tracking failed:', err);
    });
});

export default router;
