import { createRouter, createWebHistory } from 'vue-router'

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
        component: () => import('@/views/frontend/Home.vue'),
        meta: {
          title: 'Home',
          description: 'JA-CMS - Modern Content Management System',
        }
      },
      {
        path: 'blog',
        name: 'blog',
        component: () => import('@/views/frontend/Blog.vue'),
        meta: {
          title: 'Blog',
          description: 'Latest articles and updates',
        }
      },
      {
        path: 'blog/:slug',
        name: 'post',
        component: () => import('@/views/frontend/Post.vue'),
        meta: {
          title: 'Post',
        }
      },
      {
        path: 'about',
        name: 'about',
        component: () => import('@/views/frontend/About.vue'),
        meta: {
          title: 'About Us',
        }
      },
      {
        path: 'contact',
        name: 'contact',
        component: () => import('@/views/frontend/Contact.vue'),
        meta: {
          title: 'Contact',
        }
      },
      {
        path: 'search',
        name: 'search',
        component: () => import('@/views/frontend/Search.vue'),
        meta: {
          title: 'Search',
        }
      },
      // Dynamic content route (must be last in children)
      {
        path: ':slug',
        name: 'page',
        component: () => import('@/views/frontend/Post.vue'),
      },
    ]
  },
]

export default frontendRoutes
