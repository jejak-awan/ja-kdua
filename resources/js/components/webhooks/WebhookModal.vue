<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ webhook ? t('features.developer.webhooks.modal.title_edit') : t('features.developer.webhooks.modal.title_create') }}
                    </h3>
                    <button
                        @click="$emit('close')"
                        class="text-muted-foreground hover:text-muted-foreground"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.webhooks.modal.name_label') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.developer.webhooks.modal.name_placeholder')"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.webhooks.modal.url_label') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.url"
                            type="url"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                            :placeholder="t('features.developer.webhooks.modal.url_placeholder')"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.webhooks.modal.events_label') }} <span class="text-red-500">*</span>
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
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                                >
                                <span class="ml-2 text-sm text-foreground">{{ event }}</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.webhooks.modal.secret_label') }}
                        </label>
                        <input
                            v-model="form.secret"
                            type="text"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                            :placeholder="t('features.developer.webhooks.modal.secret_placeholder')"
                        >
                    </div>

                    <div class="flex items-center">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                        >
                        <label for="is_active" class="ml-2 block text-sm text-foreground">
                            {{ t('features.developer.webhooks.modal.active_label') }}
                        </label>
                    </div>
                </form>

                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                    >
                        {{ t('features.developer.webhooks.modal.cancel') }}
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50"
                    >
                        {{ saving ? t('features.developer.webhooks.modal.saving') : (webhook ? t('features.developer.webhooks.modal.update') : t('features.developer.webhooks.modal.create')) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';

const { t } = useI18n();

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
        alert(error.response?.data?.message || t('features.developer.webhooks.messages.save_failed'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadWebhook();
});
</script>

