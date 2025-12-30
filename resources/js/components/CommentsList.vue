<template>
    <div class="comments-list">
        <h3 class="text-xl font-bold text-foreground mb-4">
            {{ $t('features.comments.title') }} ({{ comments.length }})
        </h3>

        <div v-if="loading" class="text-center py-8">
            <Loader2 class="w-8 h-8 mx-auto animate-spin text-muted-foreground" />
            <p class="text-muted-foreground mt-2">{{ $t('features.comments.loading') }}</p>
        </div>

        <div v-else-if="comments.length === 0" class="text-center py-8">
            <p class="text-muted-foreground">{{ $t('features.comments.empty') }}</p>
        </div>

        <div v-else class="space-y-6">
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="border-b pb-4 last:border-b-0"
            >
                <div class="flex items-start space-x-4">
                    <Avatar>
                        <AvatarImage :src="comment.user?.avatar" :alt="comment.user?.name || comment.name" />
                        <AvatarFallback>{{ ((comment.user?.name || comment.name || '?')?.charAt(0) || '?').toUpperCase() }}</AvatarFallback>
                    </Avatar>
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="font-semibold text-foreground">
                                {{ comment.user?.name || comment.name }}
                            </span>
                            <span class="text-sm text-muted-foreground">
                                {{ formatDate(comment.created_at) }}
                            </span>
                        </div>
                        <p class="text-foreground">{{ comment.body }}</p>
                        
                        <!-- Replies -->
                        <div v-if="comment.replies && comment.replies.length > 0" class="mt-4 space-y-4">
                            <div
                                v-for="reply in comment.replies"
                                :key="reply.id"
                                class="flex items-start space-x-4 pl-4 border-l-2 border-muted"
                            >
                                <Avatar class="w-8 h-8">
                                    <AvatarImage :src="reply.user?.avatar" :alt="reply.user?.name || reply.name" />
                                    <AvatarFallback class="text-xs">{{ ((reply.user?.name || reply.name || '?')?.charAt(0) || '?').toUpperCase() }}</AvatarFallback>
                                </Avatar>
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="font-semibold text-foreground">
                                            {{ reply.user?.name || reply.name }}
                                        </span>
                                        <span class="text-sm text-muted-foreground">
                                            {{ formatDate(reply.created_at) }}
                                        </span>
                                    </div>
                                    <p class="text-foreground">{{ reply.body }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../services/api';
import { Loader2 } from 'lucide-vue-next';
import Avatar from './ui/avatar.vue';
import AvatarImage from './ui/avatar-image.vue';
import AvatarFallback from './ui/avatar-fallback.vue';

const props = defineProps({
    contentId: {
        type: [Number, String],
        required: true,
    },
});

const { t } = useI18n();
const comments = ref([]);
const loading = ref(true);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const fetchComments = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/cms/contents/${props.contentId}/comments`);
        comments.value = response.data?.data || response.data; // Handle potential wrapped response
    } catch (error) {
        console.error('Error fetching comments:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchComments();
});

defineExpose({
    refresh: fetchComments,
});
</script>


