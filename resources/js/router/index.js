import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';
import frontendRoutes from './frontend';

const routes = [
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
                path: 'contents',
                name: 'contents',
                component: () => import('../views/admin/contents/Index.vue'),
                meta: { permission: 'manage content' },
            },
            {
                path: 'contents/calendar',
                name: 'contents.calendar',
                component: () => import('../views/admin/contents/Calendar.vue'),
            },
            {
                path: 'contents/create',
                name: 'contents.create',
                component: () => import('../views/admin/contents/Create.vue'),
            },
            {
                path: 'contents/:id/edit',
                name: 'contents.edit',
                component: () => import('../views/admin/contents/Edit.vue'),
            },
            {
                path: 'contents/:id/revisions',
                name: 'contents.revisions',
                component: () => import('../views/admin/contents/Revisions.vue'),
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
                component: () => import('../views/admin/categories/Index.vue'),
            },
            {
                path: 'categories/create',
                name: 'categories.create',
                component: () => import('../views/admin/categories/Create.vue'),
            },
            {
                path: 'categories/:id/edit',
                name: 'categories.edit',
                component: () => import('../views/admin/categories/Edit.vue'),
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
                meta: { permission: 'manage roles' },
            },
            {
                path: 'roles/create',
                name: 'roles.create',
                component: () => import('../views/admin/roles/Create.vue'),
            },
            {
                path: 'roles/:id/edit',
                name: 'roles.edit',
                component: () => import('../views/admin/roles/Edit.vue'),
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
                path: 'tags',
                name: 'tags',
                component: () => import('../views/admin/tags/Index.vue'),
            },
            {
                path: 'tags/create',
                name: 'tags.create',
                component: () => import('../views/admin/tags/Create.vue'),
            },
            {
                path: 'tags/:id/edit',
                name: 'tags.edit',
                component: () => import('../views/admin/tags/Edit.vue'),
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
                path: 'content-templates',
                name: 'content-templates',
                component: () => import('../views/admin/content-templates/Index.vue'),
            },
            {
                path: 'content-templates/create',
                name: 'content-templates.create',
                component: () => import('../views/admin/content-templates/Create.vue'),
            },
            {
                path: 'content-templates/:id/edit',
                name: 'content-templates.edit',
                component: () => import('../views/admin/content-templates/Edit.vue'),
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
                path: 'command-runner',
                name: 'command-runner',
                component: () => import('../views/admin/system/CommandRunner.vue'),
                meta: { requiresSuperAdmin: true },
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
                path: 'menus/:id/edit',
                name: 'menus.edit',
                component: () => import('../views/admin/menus/Edit.vue'),
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
            {
                path: 'translations/:lang',
                name: 'translations',
                component: () => import('../views/admin/translations/Index.vue'),
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

    // Check for specific permission requirement on the target route
    if (to.meta.permission && !authStore.hasPermission(to.meta.permission)) {
        next({ name: 'forbidden' });
        return;
    }

    // Allow navigation
    next();
});

// Global error handler
router.onError((error) => {
    console.error('Router error:', error);
    router.push({ name: 'server-error', state: { errorDetails: error.message } });
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
        title: to.meta.title || document.title
    }).catch(err => {
        // Silently fail for analytics errors
        console.debug('Analytics tracking failed:', err);
    });
});

export default router;
