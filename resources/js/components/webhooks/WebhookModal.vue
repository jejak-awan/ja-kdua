<template>
    <Dialog :open="true" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>
                    {{ webhook ? t('features.developer.webhooks.modal.title_edit') : t('features.developer.webhooks.modal.title_create') }}
                </DialogTitle>
            </DialogHeader>

                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.webhooks.modal.name_label') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
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
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary font-mono text-sm"
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
                                    class="h-4 w-4 text-primary focus:ring-primary border-input rounded"
                                >
                                <span class="ml-2 text-sm text-foreground">{{ t('features.developer.webhooks.events.' + event) }}</span>
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
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary font-mono text-sm"
                            :placeholder="t('features.developer.webhooks.modal.secret_placeholder')"
                        >
                    </div>

                    <div class="flex items-center">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="h-4 w-4 text-primary focus:ring-primary border-input rounded"
                        >
                        <label for="is_active" class="ml-2 block text-sm text-foreground">
                            {{ t('features.developer.webhooks.modal.active_label') }}
                        </label>
                    </div>
                </form>

                <DialogFooter>
                    <Button variant="outline" @click="$emit('close')">
                        {{ t('features.developer.webhooks.modal.cancel') }}
                    </Button>
                    <Button
                        @click="handleSubmit"
                        :disabled="saving || !isValid || (webhook && !isDirty)"
                    >
                        <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                        {{ saving ? t('features.developer.webhooks.modal.saving') : (webhook ? t('features.developer.webhooks.modal.update') : t('features.developer.webhooks.modal.create')) }}
                    </Button>
                </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    Button
} from '@/components/ui';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

interface Webhook {
    id: number | string;
    name: string;
    url: string;
    events: string[];
    secret?: string;
    is_active: boolean;
}

const { t } = useI18n();

const props = defineProps<{
    webhook?: Webhook | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

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

interface WebhookForm {
    name: string;
    url: string;
    events: string[];
    secret: string;
    is_active: boolean;
}

const form = ref<WebhookForm>({
    name: '',
    url: '',
    events: [],
    secret: '',
    is_active: true,
});

const initialForm = ref<WebhookForm | null>(null);

const isValid = computed(() => {
    return !!form.value.name?.trim() && !!form.value.url?.trim() && form.value.events.length > 0;
});

const isDirty = computed(() => {
    if (!initialForm.value) return true;
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
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
    } else {
        form.value = {
            name: '',
            url: '',
            events: [],
            secret: '',
            is_active: true,
        };
    }
    initialForm.value = JSON.parse(JSON.stringify(form.value));
};

const toast = useToast();

const handleSubmit = async () => {
    saving.value = true;
    try {
        if (props.webhook) {
            await api.put(`/admin/ja/webhooks/${props.webhook.id}`, form.value);
            toast.success.update('Webhook');
        } else {
            await api.post('/admin/ja/webhooks', form.value);
            toast.success.create('Webhook');
        }
        emit('saved');
    } catch (error: any) {
        console.error('Failed to save webhook:', error);
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadWebhook();
});
</script>

