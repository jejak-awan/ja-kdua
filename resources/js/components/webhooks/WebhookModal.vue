<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ webhook ? 'Edit Webhook' : 'Create Webhook' }}
                    </h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Webhook name"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            URL <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.url"
                            type="url"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                            placeholder="https://example.com/webhook"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Events <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <label
                                v-for="event in availableEvents"
                                :key="event"
                                class="flex items-center"
                            >
                                <input
                                    type="checkbox"
                                    :value="event"
                                    v-model="form.events"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                >
                                <span class="ml-2 text-sm text-gray-900">{{ event }}</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Secret Key
                        </label>
                        <input
                            v-model="form.secret"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                            placeholder="Optional secret key for signing"
                        >
                    </div>

                    <div class="flex items-center">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        >
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Active
                        </label>
                    </div>
                </form>

                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? 'Saving...' : (webhook ? 'Update' : 'Create') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const props = defineProps({
    webhook: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);

const availableEvents = [
    'content.created',
    'content.updated',
    'content.deleted',
    'user.created',
    'user.updated',
    'comment.created',
    'comment.approved',
];

const form = ref({
    name: '',
    url: '',
    events: [],
    secret: '',
    is_active: true,
});

const loadWebhook = () => {
    if (props.webhook) {
        form.value = {
            name: props.webhook.name || '',
            url: props.webhook.url || '',
            events: props.webhook.events || [],
            secret: props.webhook.secret || '',
            is_active: props.webhook.is_active !== undefined ? props.webhook.is_active : true,
        };
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        if (props.webhook) {
            await api.put(`/admin/cms/webhooks/${props.webhook.id}`, form.value);
        } else {
            await api.post('/admin/cms/webhooks', form.value);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save webhook:', error);
        alert(error.response?.data?.message || 'Failed to save webhook');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadWebhook();
});
</script>

