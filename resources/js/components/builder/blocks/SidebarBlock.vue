<script setup>
import { computed } from 'vue';
import { Search, Calendar, Tag, Archive, User } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: '' },
    show_search: { type: Boolean, default: true },
    show_categories: { type: Boolean, default: true },
    show_recent_posts: { type: Boolean, default: true },
    show_tags: { type: Boolean, default: true },
    show_archive: { type: Boolean, default: false },
    categories: { type: Array, default: () => ['Technology', 'Design', 'Business', 'Marketing'] },
    recent_posts: { type: Array, default: () => [
        { title: 'Sample Post 1', date: '2024-01-15' },
        { title: 'Sample Post 2', date: '2024-01-10' },
        { title: 'Sample Post 3', date: '2024-01-05' }
    ]},
    tags: { type: Array, default: () => ['Vue', 'Laravel', 'Tailwind', 'JavaScript', 'CSS'] },
    style: { type: String, default: 'card' },
    padding: { type: String, default: 'py-8' }
});

const containerClasses = computed(() => {
    return ['transition-all duration-300', props.padding].filter(Boolean);
});

const widgetClasses = computed(() => {
    const styles = {
        card: 'bg-card border rounded-2xl p-6 shadow-sm',
        minimal: 'border-b pb-6',
        filled: 'bg-muted rounded-2xl p-6'
    };
    return styles[props.style] || styles.card;
});
</script>

<template>
    <aside :class="containerClasses">
        <div class="space-y-6">
            <!-- Title -->
            <h3 v-if="title" class="text-xl font-bold">{{ title }}</h3>
            
            <!-- Search Widget -->
            <div v-if="show_search" :class="widgetClasses">
                <h4 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-4 flex items-center gap-2">
                    <Search class="w-4 h-4" />
                    Search
                </h4>
                <div class="relative">
                    <input 
                        type="text" 
                        placeholder="Search..."
                        class="w-full h-10 pl-4 pr-10 rounded-xl border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                    <Search class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                </div>
            </div>
            
            <!-- Categories Widget -->
            <div v-if="show_categories" :class="widgetClasses">
                <h4 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-4 flex items-center gap-2">
                    <Archive class="w-4 h-4" />
                    Categories
                </h4>
                <ul class="space-y-2">
                    <li v-for="(cat, index) in categories" :key="index">
                        <a href="#" class="text-sm hover:text-primary transition-colors">{{ cat }}</a>
                    </li>
                </ul>
            </div>
            
            <!-- Recent Posts Widget -->
            <div v-if="show_recent_posts" :class="widgetClasses">
                <h4 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-4 flex items-center gap-2">
                    <Calendar class="w-4 h-4" />
                    Recent Posts
                </h4>
                <ul class="space-y-3">
                    <li v-for="(post, index) in recent_posts" :key="index">
                        <a href="#" class="block group">
                            <span class="text-sm font-medium group-hover:text-primary transition-colors line-clamp-1">{{ post.title }}</span>
                            <span class="text-xs text-muted-foreground">{{ post.date }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Tags Widget -->
            <div v-if="show_tags" :class="widgetClasses">
                <h4 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-4 flex items-center gap-2">
                    <Tag class="w-4 h-4" />
                    Tags
                </h4>
                <div class="flex flex-wrap gap-2">
                    <a 
                        v-for="(tag, index) in tags" 
                        :key="index"
                        href="#"
                        class="px-3 py-1 text-xs font-medium bg-muted rounded-full hover:bg-primary hover:text-primary-foreground transition-colors"
                    >
                        {{ tag }}
                    </a>
                </div>
            </div>
        </div>
    </aside>
</template>
