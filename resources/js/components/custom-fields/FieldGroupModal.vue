<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ fieldGroup ? t('features.developer.custom_fields.groups.modal.title_edit') : t('features.developer.custom_fields.groups.modal.title_create') }}
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
                            {{ t('features.developer.custom_fields.groups.modal.name_label') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.developer.custom_fields.groups.modal.name_placeholder')"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.custom_fields.groups.modal.description_label') }}
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.developer.custom_fields.groups.modal.description_placeholder')"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.custom_fields.groups.modal.attach_label') }}
                        </label>
                        <select
                            v-model="form.attachable_type"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option :value="null">{{ t('features.developer.custom_fields.groups.modal.attach_options.none') }}</option>
                            <option value="App\\Models\\Content">{{ t('features.developer.custom_fields.groups.modal.attach_options.content') }}</option>
                            <option value="App\\Models\\Category">{{ t('features.developer.custom_fields.groups.modal.attach_options.category') }}</option>
                            <option value="App\\Models\\Media">{{ t('features.developer.custom_fields.groups.modal.attach_options.media') }}</option>
                        </select>
                    </div>
                </form>

                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                    >
                        {{ t('features.developer.custom_fields.groups.modal.cancel') }}
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50"
                    >
                        {{ saving ? t('features.developer.custom_fields.groups.modal.saving') : (fieldGroup ? t('features.developer.custom_fields.groups.modal.update') : t('features.developer.custom_fields.groups.modal.create')) }}
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
    fieldGroup: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);

const form = ref({
    name: '',
    description: '',
    attachable_type: null,
});

const loadFieldGroup = () => {
    if (props.fieldGroup) {
        form.value = {
            name: props.fieldGroup.name || '',
            description: props.fieldGroup.description || '',
            attachable_type: props.fieldGroup.attachable_type || null,
        };
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        if (props.fieldGroup) {
            await api.put(`/admin/cms/field-groups/${props.fieldGroup.id}`, form.value);
        } else {
            await api.post('/admin/cms/field-groups', form.value);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save field group:', error);
        alert(error.response?.data?.message || t('features.developer.custom_fields.groups.messages.save_failed'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadFieldGroup();
});
</script>

