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

const { showError } = useSystemError();

const routes: Array<RouteRecordRaw> = [
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
                path: 'content-studio',
                name: 'content-studio',
                component: () => import('../views/admin/content-studio/Index.vue'),
                meta: { permission: 'manage content' },
            },
            {
                path: 'contents',
                name: 'contents',
                component: () => import('../views/admin/content-studio/contents/Index.vue'),
                meta: { permission: 'manage content' },
            },
            {
                path: 'contents/calendar',
                name: 'contents.calendar',
                component: () => import('../views/admin/content-studio/contents/Calendar.vue'),
            },
            {
                path: 'content-templates/create',
                name: 'content-templates.create',
                component: () => import('../views/admin/content-studio/templates/Create.vue'),
                meta: { permission: 'create content templates' },
            },
            {
                path: 'content-templates/:id/edit',
                name: 'content-templates.edit',
                component: () => import('../views/admin/content-studio/templates/Edit.vue'),
                meta: { permission: 'edit content templates' },
            },
            {
                path: 'contents/create',
                name: 'contents.create',
                component: () => import('../views/admin/content-studio/contents/Create.vue'),
            },
            {
                path: 'contents/:id/edit',
                name: 'contents.edit',
                component: () => import('../views/admin/content-studio/contents/Edit.vue'),
            },
            {
                path: 'contents/:id/revisions',
                name: 'contents.revisions',
                component: () => import('../views/admin/content-studio/contents/Revisions.vue'),
            },
            {
                path: 'site-editor',
                name: 'builder.site',
                component: () => import('../views/admin/builder/SiteEditor.vue'),
                meta: { permission: 'manage settings' },
            },
            {
                path: 'media',
                name: 'media',
                component: () => import('../views/admin/media/Index.vue'),
                meta: { permission: 'manage media' },
            },
            {
                path: 'categories',
                name: 'categories',
                component: () => import('../views/admin/content-studio/categories/Index.vue'),
            },
            {
                path: 'users',
                name: 'users.index',
                component: () => import('../views/admin/users/Index.vue'),
                meta: { permission: 'manage users' },
            },
            {
                path: 'users/create',
                name: 'users.create',
                component: () => import('../views/admin/users/Create.vue'),
            },
            {
                path: 'users/:id/edit',
                name: 'users.edit',
                component: () => import('../views/admin/users/Edit.vue'),
            },
            {
                path: 'roles',
                name: 'roles',
                component: () => import('../views/admin/roles/Index.vue'),
                meta: { permission: 'view roles' },
            },
            {
                path: 'roles/create',
                name: 'roles.create',
                component: () => import('../views/admin/roles/Index.vue'),
            },
            {
                path: 'roles/:id/edit',
                name: 'roles.edit',
                component: () => import('../views/admin/roles/Index.vue'),
            },
            {
                path: 'settings',
                name: 'settings',
                component: () => import('../views/admin/settings/Index.vue'),
                meta: { permission: 'manage settings' },
            },
            {
                path: 'newsletter',
                name: 'newsletter',
                component: () => import('../views/admin/newsletter/Index.vue'),
            },
            {
                path: 'profile',
                name: 'profile',
                component: () => import('../views/admin/Profile.vue'),
            },
            {
                path: 'analytics',
                name: 'analytics',
                component: () => import('../views/admin/analytics/Index.vue'),
                meta: { permission: 'view analytics' },
            },
            {
                path: 'comments',
                name: 'comments',
                component: () => import('../views/admin/comments/Index.vue'),
            },
            {
                path: 'forms',
                name: 'forms',
                component: () => import('../views/admin/forms/Index.vue'),
            },
            {
                path: 'forms/create',
                name: 'forms.create',
                component: () => import('../views/admin/forms/Create.vue'),
            },
            {
                path: 'forms/:id/edit',
                name: 'forms.edit',
                component: () => import('../views/admin/forms/Edit.vue'),
            },
            {
                path: 'forms/:id/submissions',
                name: 'forms.submissions',
                component: () => import('../views/admin/forms/SubmissionsPage.vue'),
            },
            {
                path: 'forms/:id/analytics',
                name: 'forms.analytics',
                component: () => import('../views/admin/forms/AnalyticsPage.vue'),
            },
            {
                path: 'tags',
                name: 'tags',
                component: () => import('../views/admin/content-studio/tags/Index.vue'),
            },
            {
                path: 'email-templates',
                name: 'email-templates',
                component: () => import('../views/admin/email-templates/Index.vue'),
            },
            {
                path: 'email-templates/create',
                name: 'email-templates.create',
                component: () => import('../views/admin/email-templates/Create.vue'),
            },
            {
                path: 'email-templates/:id/edit',
                name: 'email-templates.edit',
                component: () => import('../views/admin/email-templates/Edit.vue'),
            },

            {
                path: 'seo',
                name: 'seo',
                component: () => import('../views/admin/seo/Index.vue'),
            },
            {
                path: 'cache',
                name: 'cache',
                component: () => import('../views/admin/cache/Index.vue'),
            },
            {
                path: 'redirects',
                name: 'redirects',
                component: () => import('../views/admin/redirects/Index.vue'),
            },
            {
                path: 'backups',
                name: 'backups',
                component: () => import('../views/admin/backups/Index.vue'),
                meta: { permission: 'manage backups' },
            },
            {
                path: 'security',
                name: 'security',
                component: () => import('../views/admin/security/Index.vue'),
            },
            {
                path: 'security/csp-reports',
                name: 'security.csp-reports',
                component: () => import('../views/admin/security/CspReports.vue'),
                meta: { permission: 'manage settings' },
            },
            {
                path: 'security/slow-queries',
                name: 'security.slow-queries',
                component: () => import('../views/admin/security/SlowQueries.vue'),
                meta: { permission: 'manage settings' },
            },
            {
                path: 'security/dependency-vulnerabilities',
                name: 'security.dependency-vulnerabilities',
                component: () => import('../views/admin/security/DependencyVulnerabilities.vue'),
                meta: { permission: 'manage settings' },
            },
            {
                path: 'system',
                name: 'system',
                component: () => import('../views/admin/system/Index.vue'),
                meta: { permission: 'manage system' },
            },
            {
                path: 'redis',
                name: 'redis',
                component: () => import('../views/admin/system/Redis.vue'),
                meta: { title: 'Redis Management', permission: 'manage settings' },
            },
            {
                path: 'system/notifications',
                name: 'system-notifications',
                component: () => import('../views/admin/system/NotificationManager.vue'),
                meta: { title: 'Notification Manager', permission: 'manage system' },
            },
            {
                path: 'activity-logs',
                name: 'activity-logs',
                component: () => import('../views/admin/activity-logs/Index.vue'),
            },
            {
                path: 'login-history',
                name: 'login-history',
                component: () => import('../views/admin/login-history/Index.vue'),
            },
            {
                path: 'logs-dashboard',
                name: 'logs-dashboard',
                component: () => import('../views/admin/logs-dashboard/Index.vue'),
            },
            {
                path: 'notifications',
                name: 'notifications',
                component: () => import('../views/admin/notifications/Index.vue'),
            },
            {
                path: 'scheduled-tasks',
                name: 'scheduled-tasks',
                component: () => import('../views/admin/system/ScheduledTasks.vue'),
                meta: { permission: 'manage scheduled tasks' },
            },

            {
                path: 'logs',
                name: 'logs',
                component: () => import('../views/admin/logs/Index.vue'),
            },
            {
                path: 'webhooks',
                name: 'webhooks',
                component: () => import('../views/admin/webhooks/Index.vue'),
            },
            {
                path: 'custom-fields',
                name: 'custom-fields',
                component: () => import('../views/admin/custom-fields/Index.vue'),
            },
            {
                path: 'file-manager',
                name: 'file-manager',
                component: () => import('../views/admin/file-manager/Index.vue'),
            },
            {
                path: 'search',
                name: 'search',
                component: () => import('../views/admin/search/Index.vue'),
            },
            {
                path: 'themes',
                name: 'themes',
                component: () => import('../views/admin/themes/Index.vue'),
                meta: { permission: 'manage themes' },
            },


            {
                path: 'menus',
                name: 'menus',
                component: () => import('../views/admin/menus/Index.vue'),
                meta: { permission: 'manage menus' },
            },

            {
                path: 'widgets',
                name: 'widgets',
                component: () => import('../views/admin/widgets/Index.vue'),
                meta: { permission: 'manage widgets' },
            },
            {
                path: 'plugins',
                name: 'plugins',
                component: () => import('../views/admin/plugins/Index.vue'),
                meta: { permission: 'manage plugins' },
            },
            {
                path: 'languages',
                name: 'languages',
                component: () => import('../views/admin/languages/Index.vue'),
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
        console.debug('Analytics tracking failed:', err);
    });
});

export default router;
