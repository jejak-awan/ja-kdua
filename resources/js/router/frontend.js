import { createRouter, createWebHistory } from 'vue-router'

// Theme Page Resolver
const ThemePageResolver = () => import('@/components/ThemePageResolver.vue')

// Frontend theme routes
const frontendRoutes = [
  {
    path: '/',
    name: 'frontend',
    component: () => import('@/layouts/FrontendLayout.vue'),
    children: [
      {
        path: '',
        name: 'home',
        component: ThemePageResolver,
        props: { page: 'Home' },
        meta: {
          title: 'Home',
          description: 'JA-CMS - Modern Content Management System',
        }
      },
      {
        path: 'blog',
        name: 'blog',
        component: ThemePageResolver,
        props: { page: 'Blog' },
        meta: {
          title: 'Blog',
          description: 'Latest articles and updates',
        }
      },
      {
        path: 'blog/:slug',
        name: 'post',
        component: ThemePageResolver,
        props: { page: 'Post' },
        meta: {
          title: 'Post',
        }
      },
      {
        path: 'about',
        name: 'about',
        component: ThemePageResolver,
        props: { page: 'About' },
        meta: {
          title: 'About Us',
        }
      },
      {
        path: 'contact',
        name: 'contact',
        component: ThemePageResolver,
        props: { page: 'Contact' },
        meta: {
          title: 'Contact',
        }
      },
      {
        path: 'search',
        name: 'search',
        component: ThemePageResolver,
        props: { page: 'Search' },
        meta: {
          title: 'Search',
        }
      },
      // Dynamic content route (must be last in children)
      {
        path: ':slug',
        name: 'page',
        component: ThemePageResolver,
        props: { page: 'Post' }, // Reusing Post component for generic Pages usually
      },
    ]
  },
]

export default frontendRoutes
