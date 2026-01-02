<template>
    <div :style="style" :class="['flex flex-wrap items-center gap-x-4 gap-y-2 text-sm', config.alignment]">
        <!-- Author -->
        <div v-if="config.showAuthor" class="flex items-center gap-1.5 transition-colors hover:text-primary cursor-default">
            <User class="w-4 h-4 text-muted-foreground" />
            <span class="font-medium text-foreground">{{ authorName }}</span>
        </div>

        <!-- Date -->
        <div v-if="config.showDate" class="flex items-center gap-1.5 text-muted-foreground">
            <Calendar class="w-4 h-4" />
            <span>{{ formattedDate }}</span>
        </div>

        <!-- Categories -->
        <div v-if="config.showCategories && categories.length" class="flex items-center gap-1.5">
            <FolderOpen class="w-4 h-4 text-muted-foreground" />
            <div class="flex gap-1">
                <span v-for="(cat, i) in categories" :key="cat.id" class="inline-flex items-center text-primary font-medium">
                    {{ cat.name }}{{ i < categories.length - 1 ? ',' : '' }}
                </span>
            </div>
        </div>
        
        <!-- Comments count (Optional) -->
        <div v-if="config.showComments" class="flex items-center gap-1.5 text-muted-foreground">
            <MessageSquare class="w-4 h-4" />
            <span>{{ commentCount }} Comments</span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { User, Calendar, FolderOpen, MessageSquare } from 'lucide-vue-next';

const props = defineProps({
    config: {
        type: Object,
        default: () => ({
            showAuthor: true,
            showDate: true,
            showCategories: true,
            showComments: false,
            alignment: 'justify-start',
            color: ''
        })
    },
    context: {
        type: Object,
        default: () => ({})
    }
});

const post = computed(() => props.context?.post);

const authorName = computed(() => {
    if (post.value?.author?.name) return post.value.author.name;
    if (props.context?.builderMode) return 'Admin User';
    return 'Anonymous';
});

const formattedDate = computed(() => {
    if (post.value?.created_at) {
        return new Date(post.value.created_at).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
    if (props.context?.builderMode) return 'January 1, 2026';
    return '-';
});

const categories = computed(() => {
    if (post.value?.categories) return post.value.categories;
    if (props.context?.builderMode) return [{ id: 1, name: 'General' }, { id: 2, name: 'Tips' }];
    return [];
});

const commentCount = computed(() => post.value?.comments_count || 0);

const style = computed(() => ({
    color: props.config.color || 'inherit'
}));
</script>
