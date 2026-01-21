<template>
    <div class="sidebar-block flex flex-col gap-8 w-full">
        <div 
            v-for="(widget, index) in widgets" 
            :key="index"
            class="sidebar-widget p-6 bg-card rounded-xl border shadow-sm"
        >
            <h4 
                v-if="showTitle !== false && widget.title" 
                class="font-bold text-lg mb-4 border-b pb-2"
            >
                {{ widget.title }}
            </h4>

            <!-- Search Widget -->
            <div v-if="widget.widgetType === 'search'" class="relative">
                <input 
                    type="search" 
                    placeholder="Search..." 
                    class="w-full pl-4 pr-10 py-3 rounded-lg border bg-background focus:ring-2 focus:ring-primary/20 outline-none"
                />
                <Search class="absolute right-3 top-3.5 w-5 h-5 text-muted-foreground" />
            </div>

            <!-- Categories / Recent Posts / Tags -->
            <ul v-else-if="['categories', 'recentposts'].includes(widget.widgetType)" class="space-y-2">
                <li v-for="i in Number(widget.count || 5)" :key="i" class="flex items-center gap-2 group cursor-pointer">
                    <ChevronRight class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                    <span class="text-sm opacity-80 group-hover:opacity-100 transition-opacity">
                        {{ widget.widgetType === 'categories' ? `Category ${i}` : `Recent Post Title ${i}` }}
                    </span>
                </li>
            </ul>

            <!-- Tag Cloud -->
            <div v-else-if="widget.widgetType === 'tags'" class="flex flex-wrap gap-2">
                <span 
                    v-for="i in Number(widget.count || 8)" 
                    :key="i"
                    class="px-3 py-1 bg-muted rounded-full text-xs font-medium hover:bg-primary hover:text-white transition-colors cursor-pointer"
                >
                    Tag {{ i }}
                </span>
            </div>

            <!-- Generic Text/HTML -->
            <div v-else-if="widget.widgetType === 'text'" class="prose prose-sm dark:prose-invert">
                 <div v-html="widget.content || '<p>Widget content goes here.</p>'"></div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { Search, ChevronRight } from 'lucide-vue-next';

defineProps({
    showTitle: { type: Boolean, default: true },
    widgets: { type: Array, default: () => [] }
});
</script>
