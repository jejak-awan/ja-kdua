import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
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
            },
            {
                path: 'categories',
                name: 'categories',
                component: () => import('../views/admin/categories/Index.vue'),
            },
            {
                path: 'users',
                name: 'users',
                component: () => import('../views/admin/users/Index.vue'),
            },
            {
                path: 'settings',
                name: 'settings',
                component: () => import('../views/admin/settings/Index.vue'),
            },
            {
                path: 'analytics',
                name: 'analytics',
                component: () => import('../views/admin/analytics/Index.vue'),
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
                path: 'tags',
                name: 'tags',
                component: () => import('../views/admin/tags/Index.vue'),
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
            },
            {
                path: 'activity-logs',
                name: 'activity-logs',
                component: () => import('../views/admin/activity-logs/Index.vue'),
            },
            {
                path: 'notifications',
                name: 'notifications',
                component: () => import('../views/admin/notifications/Index.vue'),
            },
            {
                path: 'scheduled-tasks',
                name: 'scheduled-tasks',
                component: () => import('../views/admin/scheduled-tasks/Index.vue'),
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
            },
            {
                path: 'menus',
                name: 'menus',
                component: () => import('../views/admin/menus/Index.vue'),
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
            },
            {
                path: 'plugins',
                name: 'plugins',
                component: () => import('../views/admin/plugins/Index.vue'),
            },
            {
                path: 'languages',
                name: 'languages',
                component: () => import('../views/admin/languages/Index.vue'),
            },
            {
                path: 'translations/:lang',
                name: 'translations',
                component: () => import('../views/admin/translations/Index.vue'),
            },
        ],
    },
    {
        path: '/content/:slug',
        name: 'content.show',
        component: () => import('../views/ContentShow.vue'),
        meta: { public: true },
    },
    {
        path: '/',
        name: 'home',
        component: () => import('../views/Home.vue'),
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
    
    // Public routes (home, etc.) - skip auth check
    if (to.meta.public) {
        next();
        return;
    }
    
    // Initialize auth for protected routes
    authStore.initAuth();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login', query: { redirect: to.fullPath } });
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next({ name: 'dashboard' });
    } else {
        next();
    }
});

export default router;
