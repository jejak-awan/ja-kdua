<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ task ? t('features.system.scheduled_tasks.modal.title_edit') : t('features.system.scheduled_tasks.modal.title_create') }}
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
                            {{ t('features.system.scheduled_tasks.modal.name_label') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.system.scheduled_tasks.modal.name_placeholder')"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.system.scheduled_tasks.modal.command_label') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.command"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                            :placeholder="t('features.system.scheduled_tasks.modal.command_placeholder')"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">{{ t('features.system.scheduled_tasks.modal.command_help') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.system.scheduled_tasks.modal.schedule_label') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.schedule"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                            :placeholder="t('features.system.scheduled_tasks.modal.schedule_placeholder')"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ t('features.system.scheduled_tasks.modal.schedule_help') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.system.scheduled_tasks.modal.description_label') }}
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.system.scheduled_tasks.modal.description_placeholder')"
                        />
                    </div>

                    <div class="flex items-center">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                        >
                        <label for="is_active" class="ml-2 block text-sm text-foreground">
                            {{ t('features.system.scheduled_tasks.modal.active_label') }}
                        </label>
                    </div>
                </form>

                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                    >
                        {{ t('features.system.scheduled_tasks.modal.cancel') }}
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50"
                    >
                        {{ saving ? t('features.system.scheduled_tasks.modal.saving') : (task ? t('features.system.scheduled_tasks.modal.update') : t('features.system.scheduled_tasks.modal.create_action')) }}
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
    task: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);

const form = ref({
    name: '',
    command: '',
    schedule: '',
    description: '',
    is_active: true,
});

const loadTask = () => {
    if (props.task) {
        form.value = {
            name: props.task.name || '',
            command: props.task.command || '',
            schedule: props.task.schedule || '',
            description: props.task.description || '',
            is_active: props.task.is_active !== undefined ? props.task.is_active : true,
        };
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        if (props.task) {
            await api.put(`/admin/cms/scheduled-tasks/${props.task.id}`, form.value);
        } else {
            await api.post('/admin/cms/scheduled-tasks', form.value);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save task:', error);
        alert(error.response?.data?.message || t('features.system.scheduled_tasks.messages.failed_save'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadTask();
});
</script>

