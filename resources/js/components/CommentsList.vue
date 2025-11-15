<template>
    <div class="comments-list">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Comments ({{ comments.length }})</h3>

        <div v-if="loading" class="text-center py-8">
            <p class="text-gray-500">Loading comments...</p>
        </div>

        <div v-else-if="comments.length === 0" class="text-center py-8">
            <p class="text-gray-500">No comments yet. Be the first to comment!</p>
        </div>

        <div v-else class="space-y-6">
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="border-b pb-4 last:border-b-0"
            >
                <div class="flex items-start space-x-4">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="font-semibold text-gray-900">
                                {{ comment.user?.name || comment.name }}
                            </span>
                            <span class="text-sm text-gray-500">
                                {{ formatDate(comment.created_at) }}
                            </span>
                        </div>
                        <p class="text-gray-700">{{ comment.body }}</p>
                        
                        <!-- Replies -->
                        <div v-if="comment.replies && comment.replies.length > 0" class="mt-4 ml-8 space-y-4">
                            <div
                                v-for="reply in comment.replies"
                                :key="reply.id"
                                class="border-l-2 border-gray-200 pl-4"
                            >
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="font-semibold text-gray-900">
                                        {{ reply.user?.name || reply.name }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        {{ formatDate(reply.created_at) }}
                                    </span>
                                </div>
                                <p class="text-gray-700">{{ reply.body }}</p>
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
import axios from 'axios';

const props = defineProps({
    contentId: {
        type: [Number, String],
        required: true,
    },
});

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
        const response = await axios.get(`/api/v1/cms/contents/${props.contentId}/comments`);
        comments.value = response.data;
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

