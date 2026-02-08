<template>
  <div class="min-h-screen flex flex-col">
    <!-- Single Loading State -->
    <div v-if="loading" class="flex-1 flex items-center justify-center min-h-[60vh]">
        <div class="flex flex-col items-center gap-4">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
            <span class="text-muted-foreground text-sm">Loading...</span>
        </div>
    </div>
    
    <template v-else>
        <!-- Dynamic Content if exists -->
        <BlockRenderer 
            v-if="pageData && pageData.blocks && pageData.blocks.length > 0" 
            :blocks="pageData.blocks"
            :context="{ page: pageData }"
            :is-preview="true"
        />
        
        <!-- ========== PREMIUM FALLBACK - JA-CMS SHOWCASE ========== -->
        <div v-else class="flex-1 flex flex-col">
<!-- HERO SECTION -->
            <section class="relative py-32 lg:py-48 overflow-hidden bg-gradient-to-b from-primary/10 via-background to-background dark:from-primary/20">
                <!-- Background Decorative Elements -->
                <div class="absolute inset-0 -z-10 overflow-hidden">
                    <!-- Top gradient blob -->
                    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[800px] h-[600px] rounded-full bg-primary/20 dark:bg-primary/30 blur-3xl"></div>
                    <!-- Side decorative blobs -->
                    <div class="absolute top-1/4 -left-32 w-96 h-96 rounded-full bg-violet-500/20 dark:bg-violet-500/30 blur-3xl"></div>
                    <div class="absolute top-1/3 -right-32 w-96 h-96 rounded-full bg-rose-500/20 dark:bg-rose-500/30 blur-3xl"></div>
                </div>

                <div class="container mx-auto px-4 text-center relative z-10">
                    <span class="inline-flex items-center rounded-full px-4 py-1.5 text-sm font-semibold bg-primary/10 text-primary mb-8 ring-1 ring-inset ring-primary/20 backdrop-blur-sm">
                        🚀 {{ siteVersion }}
                    </span>
                    <h1 class="text-5xl md:text-7xl font-bold tracking-tight text-foreground mb-6 text-balance">
                        {{ heroTitle }}
                    </h1>
                    <p class="text-xl md:text-2xl text-muted-foreground max-w-3xl mx-auto mb-4 font-medium">
                        {{ heroSubtitle }}
                    </p>
                    <p class="text-lg text-muted-foreground max-w-2xl mx-auto mb-10">
                        {{ siteTagline }}
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <router-link 
                            to="/register" 
                            class="rounded-full bg-primary px-8 py-4 text-sm font-semibold text-primary-foreground shadow-lg hover:bg-primary/90 transition-colors duration-300 hover:scale-105 hover:shadow-xl"
                        >
                            Get Started Free
                        </router-link>
                        <router-link 
                            to="/about"
                            class="rounded-full border border-border px-8 py-4 text-sm font-semibold text-foreground hover:bg-muted transition-colors duration-300 flex items-center gap-2"
                        >
                            Learn More <span class="text-primary">→</span>
                        </router-link>
                    </div>
                </div>
            </section>

            <!-- ABOUT JA-CMS SECTION -->
            <section class="py-24 bg-muted/50 dark:bg-muted/20">
                <div class="container mx-auto px-4">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                        <div class="space-y-6">
                            <span class="text-primary font-bold tracking-wider uppercase text-sm">Tentang JA-CMS</span>
                            <h2 class="text-4xl font-bold text-foreground">
                                CMS Modern untuk Era Digital
                            </h2>
                            <p class="text-lg text-muted-foreground leading-relaxed">
                                JA-CMS adalah Content Management System generasi baru yang dibangun dengan teknologi terkini. 
                                Menggabungkan kekuatan Laravel di backend dan Vue.js di frontend untuk pengalaman 
                                pengembangan yang seamless.
                            </p>
                            <p class="text-muted-foreground leading-relaxed">
                                Dirancang untuk developer yang menginginkan kecepatan, fleksibilitas, dan kemudahan 
                                kustomisasi tanpa mengorbankan performa.
                            </p>
                            <div class="grid grid-cols-3 gap-6 pt-6 border-t border-border">
                                <div>
                                    <div class="text-3xl font-bold text-primary">50+</div>
                                    <div class="text-sm text-muted-foreground mt-1">Components</div>
                                </div>
                                <div>
                                    <div class="text-3xl font-bold text-primary">10x</div>
                                    <div class="text-sm text-muted-foreground mt-1">Faster</div>
                                </div>
                                <div>
                                    <div class="text-3xl font-bold text-primary">100%</div>
                                    <div class="text-sm text-muted-foreground mt-1">Open Source</div>
                                </div>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-transparent rounded-2xl transform rotate-3"></div>
                            <div class="relative rounded-2xl bg-card border border-border p-8 shadow-2xl">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                </div>
                                <pre class="text-sm text-muted-foreground font-mono overflow-x-auto"><code>// Create content with ease
const content = await Content.create({
  title: 'Welcome to JA-CMS',
  type: 'page',
  status: 'published',
  blocks: [...]
});

// Blazing fast queries
Content::published()
  ->with('author', 'tags')
  ->paginate(12);</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FEATURES SECTION -->
            <section class="py-24 bg-background">
                <div class="container mx-auto px-4">
                    <div class="text-center mb-16">
                        <span class="text-primary font-bold tracking-wider uppercase text-sm">Fitur</span>
                        <h2 class="text-4xl font-bold mt-4 mb-6">Fitur Powerful</h2>
                        <p class="text-muted-foreground max-w-2xl mx-auto">
                            Everything you need to build and manage modern websites
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="feature in features" :key="feature.title" class="p-8 rounded-2xl bg-card border border-border shadow-sm hover:shadow-xl transition-colors duration-300 hover:-translate-y-1 group">
                            <div class="w-14 h-14 rounded-xl bg-primary/20 dark:bg-primary/30 flex items-center justify-center mb-6 text-primary group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                                <component :is="feature.icon" class="w-7 h-7" />
                            </div>
                            <h3 class="text-xl font-bold mb-3">{{ feature.title }}</h3>
                            <p class="text-muted-foreground">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ADVANTAGES SECTION -->
            <section class="py-24 bg-muted/40 dark:bg-muted/20">
                <div class="container mx-auto px-4">
                    <div class="text-center mb-16">
                        <span class="text-primary font-bold tracking-wider uppercase text-sm">Keunggulan</span>
                        <h2 class="text-4xl font-bold mt-4 mb-6">Mengapa JA-CMS?</h2>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                        <div v-for="(advantage, idx) in advantages" :key="idx" class="flex gap-6">
                            <div class="shrink-0 w-12 h-12 rounded-full bg-primary text-primary-foreground flex items-center justify-center font-bold text-lg shadow-lg">
                                {{ idx + 1 }}
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2 text-foreground">{{ advantage.title }}</h3>
                                <p class="text-muted-foreground">{{ advantage.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- DEV TEAM SECTION -->
            <section class="py-24 bg-background">
                <div class="container mx-auto px-4 text-center">
                    <span class="text-primary font-bold tracking-wider uppercase text-sm">Tim Developer</span>
                    <h2 class="text-4xl font-bold mt-4 mb-6">Meet Our Team</h2>
                    <p class="text-muted-foreground max-w-xl mx-auto mb-16">
                        The passionate developers behind JA-CMS
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                        <div v-for="member in teamMembers" :key="member.name" class="group">
                            <div class="w-full aspect-square rounded-2xl bg-muted/70 dark:bg-muted/50 mb-4 overflow-hidden relative border border-border">
                                <img 
                                    v-if="member.image" 
                                    :src="member.image" 
                                    :alt="member.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                >
                                <div v-else class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-6xl font-bold text-foreground/20 dark:text-foreground/30">
                                        {{ member.initials }}
                                    </span>
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </div>
                            <h3 class="font-bold text-lg text-foreground">{{ member.name }}</h3>
                            <p class="text-sm text-muted-foreground">{{ member.role }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA SECTION -->
            <section class="py-24 bg-slate-900 dark:bg-primary relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/30 to-transparent dark:from-white/10 dark:to-transparent"></div>
                <div class="container mx-auto px-4 text-center relative z-10">
                    <h2 class="text-4xl md:text-5xl font-bold mb-6 text-white">Siap Memulai?</h2>
                    <p class="text-xl text-white/80 max-w-2xl mx-auto mb-10">
                        Mulai bangun website impianmu dengan JA-CMS. Gratis, open source, dan powerful.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <router-link 
                            to="/register" 
                            class="rounded-full bg-white text-slate-900 px-8 py-4 text-sm font-bold shadow-lg hover:bg-white/90 transition-colors duration-300 hover:scale-105"
                        >
                            Daftar Sekarang
                        </router-link>
                        <a 
                            href="https://github.com/ja-cms" 
                            target="_blank"
                            class="rounded-full border-2 border-white/50 px-8 py-4 text-sm font-semibold text-white hover:bg-white/10 transition-colors duration-300 flex items-center gap-2"
                        >
                            <Github class="w-5 h-5" />
                            Star on GitHub
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'
import BlockRenderer from '@/components/content-renderer/BlockRenderer.vue'
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import Palette from 'lucide-vue-next/dist/esm/icons/palette.js';
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import Shield from 'lucide-vue-next/dist/esm/icons/shield.js';
import Code2 from 'lucide-vue-next/dist/esm/icons/code-xml.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import Github from 'lucide-vue-next/dist/esm/icons/github.js';
import type { Component } from 'vue'
import type { Content } from '@/types/cms'

import type { BlockInstance } from '@/types/builder'

interface Feature {
    icon: Component;
    title: string;
    description: string;
}

interface Advantage {
    title: string;
    description: string;
}

interface TeamMember {
    name: string;
    initials: string;
    role: string;
    image: string;
}

interface PageData extends Content {
    blocks?: BlockInstance[]; 
}

// useTheme and useCmsStore for dynamic fallback branding
import { useTheme } from '@/composables/useTheme';
import { useCmsStore } from '@/stores/cms';
const { getSetting } = useTheme();
const cmsStore = useCmsStore();

const siteTagline = computed(() => (getSetting('site_tagline') as string) || cmsStore.siteSettings?.site_tagline || cmsStore.siteSettings?.site_description || 'Modern & Elegant CMS');
const heroTitle = computed(() => (getSetting('hero_title') as string) || (getSetting('site_title') as string) || cmsStore.siteSettings?.site_name || 'JA-CMS');
const heroSubtitle = computed(() => (getSetting('hero_subtitle') as string) || 'Modern Content Management System');
const siteVersion = computed(() => cmsStore.siteSettings?.site_version || 'v1.0 Janari Edition');

const pageData = ref<PageData | null>(null)
const loading = ref(true)

// Features data
const features: Feature[] = [
    {
        icon: Zap,
        title: 'Blazing Fast',
        description: 'Optimized for speed with smart caching, lazy loading, and efficient database queries.'
    },
    {
        icon: Palette,
        title: 'Theme System',
        description: 'Flexible theming with Janari Theme included. Dark mode, custom colors, and more.'
    },
    {
        icon: Layers,
        title: 'Block Builder',
        description: 'Visual page builder with drag-and-drop blocks. No coding required.'
    },
    {
        icon: Shield,
        title: 'Secure by Default',
        description: 'Built-in authentication, role-based permissions, and security best practices.'
    },
    {
        icon: Code2,
        title: 'Developer Friendly',
        description: 'Clean code, extensive documentation, and easy customization for developers.'
    },
    {
        icon: Globe,
        title: 'Multi-language',
        description: 'Full i18n support. Build multilingual websites with ease.'
    }
]

// Advantages data
const advantages: Advantage[] = [
    {
        title: 'Modern Tech Stack',
        description: 'Laravel 11 + Vue 3 + Tailwind CSS. The best tools for modern web development.'
    },
    {
        title: 'Zero Configuration',
        description: 'Works out of the box. No complex setup required. Just deploy and start building.'
    },
    {
        title: 'SEO Optimized',
        description: 'Built-in SEO tools, meta tags, sitemaps, and structured data support.'
    },
    {
        title: 'API-First Design',
        description: 'RESTful API ready for headless CMS usage or mobile app integration.'
    }
]

// Team members data
const teamMembers: TeamMember[] = [
    { name: 'Ari Nurcahya', initials: 'AN', role: 'Lead Developer', image: '/images/fallback/team-1.png' },
    { name: 'Sarah Amira', initials: 'SA', role: 'Frontend Engineer', image: '/images/fallback/team-2.png' },
    { name: 'Budi Santoso', initials: 'BS', role: 'Backend Engineer', image: '/images/fallback/team-3.png' },
    { name: 'Maya Putri', initials: 'MP', role: 'UI/UX Designer', image: '/images/fallback/team-4.png' }
]

onMounted(async () => {
  try {
    const response = await api.get('/cms/contents/home')
    pageData.value = response.data.data
  } catch (error: unknown) {
    // Gracefully handle - fallback will render
    if (typeof error === 'object' && error !== null && 'response' in error) {
      const err = error as { response?: { status?: number } };
      if (err.response?.status !== 404 && err.response?.status !== 200) {
        logger.error('Failed to fetch home page:', error);
      }
    } else {
      logger.error('Failed to fetch home page:', error);
    }
  } finally {
    loading.value = false
  }
})
</script>

