<template>
    <div class="comment-form">
        <h3 class="text-lg font-semibold text-foreground mb-4">Leave a Comment</h3>
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div v-if="!authStore.isAuthenticated" class="grid grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-foreground">Name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-foreground">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>
            </div>

            <div>
                <label for="body" class="block text-sm font-medium text-foreground">Comment</label>
                <textarea
                    id="body"
                    v-model="form.body"
                    rows="4"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
            </div>

            <div>
                <button
                    type="submit"
                    :disabled="loading"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ loading ? 'Posting...' : 'Post Comment' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const props = defineProps({
    contentId: {
        type: [Number, String],
        required: true,
    },
    parentId: {
        type: [Number, String],
        default: null,
    },
});

const emit = defineEmits(['submitted']);

const authStore = useAuthStore();

const form = ref({
    name: '',
    email: '',
    body: '',
    parent_id: props.parentId,
});

const loading = ref(false);

const handleSubmit = async () => {
    loading.value = true;
    try {
        await axios.post(`/api/v1/cms/contents/${props.contentId}/comments`, form.value);
        
        // Reset form
        form.value = {
            name: '',
            email: '',
            body: '',
            parent_id: props.parentId,
        };

        emit('submitted');
    } catch (error) {
        console.error('Error posting comment:', error);
        alert('Failed to post comment. Please try again.');
    } finally {
        loading.value = false;
    }
};
</script>

