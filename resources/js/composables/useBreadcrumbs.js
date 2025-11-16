import { ref, computed } from 'vue';

// Breadcrumb configuration for custom labels
const breadcrumbConfig = {
    // Admin routes
    '/admin': 'Dashboard',
    '/admin/contents': 'Konten',
    '/admin/contents/create': 'Buat Konten',
    '/admin/contents/:id/edit': 'Edit Konten',
    '/admin/contents/:id/revisions': 'Riwayat Revisi',
    '/admin/media': 'Media',
    '/admin/categories': 'Kategori',
    '/admin/tags': 'Tag',
    '/admin/users': 'Pengguna',
    '/admin/comments': 'Komentar',
    '/admin/forms': 'Form',
    '/admin/forms/:id/submissions': 'Submission Form',
    '/admin/email-templates': 'Template Email',
    '/admin/email-templates/create': 'Buat Template Email',
    '/admin/email-templates/:id/edit': 'Edit Template Email',
    '/admin/content-templates': 'Template Konten',
    '/admin/content-templates/create': 'Buat Template Konten',
    '/admin/content-templates/:id/edit': 'Edit Template Konten',
    '/admin/seo': 'SEO',
    '/admin/redirects': 'Redirect',
    '/admin/analytics': 'Analitik',
    '/admin/themes': 'Tema',
    '/admin/menus': 'Menu',
    '/admin/menus/:id/edit': 'Edit Menu',
    '/admin/widgets': 'Widget',
    '/admin/plugins': 'Plugin',
    '/admin/languages': 'Bahasa',
    '/admin/translations/:lang': 'Terjemahan',
    '/admin/custom-fields': 'Custom Fields',
    '/admin/file-manager': 'File Manager',
    '/admin/search': 'Pencarian',
    '/admin/settings': 'Pengaturan',
    '/admin/security': 'Keamanan',
    '/admin/system': 'Sistem',
    '/admin/redis': 'Redis',
    '/admin/backups': 'Backup',
    '/admin/activity-logs': 'Log Aktivitas',
    '/admin/notifications': 'Notifikasi',
    '/admin/scheduled-tasks': 'Tugas Terjadwal',
    '/admin/logs': 'Log Sistem',
    '/admin/webhooks': 'Webhook',
    '/admin/cache': 'Cache',
    
    // Frontend routes
    '/': 'Beranda',
    '/blog': 'Blog',
    '/about': 'Tentang Kami',
    '/contact': 'Kontak',
    '/search': 'Hasil Pencarian',
    
    // Auth routes
    '/login': 'Login',
    '/register': 'Registrasi',
    '/forgot-password': 'Lupa Password',
    '/reset-password': 'Reset Password',
};

export function useBreadcrumbs() {
    const customBreadcrumbs = ref({});
    
    /**
     * Get breadcrumb label for a path
     */
    const getLabel = (path, route) => {
        // Check custom breadcrumbs first
        if (customBreadcrumbs.value[path]) {
            return customBreadcrumbs.value[path];
        }
        
        // Check route meta
        if (route?.meta?.breadcrumb) {
            return route.meta.breadcrumb;
        }
        
        // Check configuration
        if (breadcrumbConfig[path]) {
            return breadcrumbConfig[path];
        }
        
        // Try to match dynamic routes (e.g., /admin/contents/:id/edit)
        for (const [pattern, label] of Object.entries(breadcrumbConfig)) {
            if (pattern.includes(':')) {
                const regex = new RegExp('^' + pattern.replace(/:\w+/g, '[^/]+') + '$');
                if (regex.test(path)) {
                    return label;
                }
            }
        }
        
        // Fallback: capitalize and clean path segment
        const segment = path.split('/').pop();
        return segment
            ? segment
                .replace(/-/g, ' ')
                .replace(/\b\w/g, (l) => l.toUpperCase())
            : 'Home';
    };
    
    /**
     * Build breadcrumb path from route segments
     */
    const buildPath = (segments, index) => {
        return '/' + segments.slice(0, index + 1).join('/');
    };
    
    /**
     * Generate breadcrumbs from route
     */
    const getBreadcrumbs = (route) => {
        if (!route || !route.path) return [];
        
        const breadcrumbs = [];
        const pathSegments = route.path.split('/').filter(Boolean);
        
        // Always add home for admin routes
        if (route.path.startsWith('/admin')) {
            breadcrumbs.push({
                label: 'Home',
                path: '/admin',
            });
        }
        // Add home for frontend routes (except home itself)
        else if (route.path !== '/') {
            breadcrumbs.push({
                label: 'Home',
                path: '/',
            });
        }
        // If we're on home page, just return empty (or single home item)
        else {
            return [{
                label: 'Home',
                path: '/',
            }];
        }
        
        // Build breadcrumbs from path segments
        pathSegments.forEach((segment, index) => {
            // Skip 'admin' as it's already added as home
            if (segment === 'admin' && index === 0) return;
            
            const path = buildPath(pathSegments, index);
            const label = getLabel(path, route);
            
            // Skip if same as previous (avoid duplicates)
            if (breadcrumbs.length > 0 && breadcrumbs[breadcrumbs.length - 1].path === path) {
                return;
            }
            
            breadcrumbs.push({
                label,
                path,
            });
        });
        
        // If route has custom title in meta, use it for last item
        if (route.meta?.title && breadcrumbs.length > 0) {
            breadcrumbs[breadcrumbs.length - 1].label = route.meta.title;
        }
        
        return breadcrumbs;
    };
    
    /**
     * Set custom breadcrumb label for a path
     */
    const setBreadcrumb = (path, label) => {
        customBreadcrumbs.value[path] = label;
    };
    
    /**
     * Set multiple custom breadcrumbs at once
     */
    const setBreadcrumbs = (breadcrumbs) => {
        customBreadcrumbs.value = {
            ...customBreadcrumbs.value,
            ...breadcrumbs,
        };
    };
    
    /**
     * Clear custom breadcrumbs
     */
    const clearBreadcrumbs = () => {
        customBreadcrumbs.value = {};
    };
    
    return {
        getBreadcrumbs,
        setBreadcrumb,
        setBreadcrumbs,
        clearBreadcrumbs,
        getLabel,
    };
}

