<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg shadow-xl max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ widget ? $t('features.widgets.modals.widget.titleEdit') : $t('features.widgets.modals.widget.titleCreate') }}</h3>
                    <button @click="$emit('close')" class="text-gray-400 hover:text-muted-foreground">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ $t('features.widgets.modals.widget.name') }} <span class="text-red-500">*</span></label>
                        <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ $t('features.widgets.modals.widget.type') }} <span class="text-red-500">*</span></label>
                        <select v-model="form.type" required class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="text">Text</option>
                            <option value="html">HTML</option>
                            <option value="menu">Menu</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ $t('features.widgets.modals.widget.position') }}</label>
                        <input v-model="form.position" type="text" class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" :placeholder="$t('features.widgets.modals.widget.positionPlaceholder')">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ $t('features.widgets.modals.widget.content') }}</label>
                        <textarea v-model="form.content" rows="6" class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>
                    <div class="flex items-center">
                        <input v-model="form.is_active" type="checkbox" id="is_active" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded">
                        <label for="is_active" class="ml-2 block text-sm text-foreground">{{ $t('features.widgets.modals.widget.active') }}</label>
                    </div>
                </form>
                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button @click="$emit('close')" class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted">{{ $t('common.cancel') }}</button>
                    <button @click="handleSubmit" :disabled="saving" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">{{ saving ? $t('features.widgets.modals.widget.saving') : (widget ? $t('common.update') : $t('common.create')) }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';

const props = defineProps({ widget: { type: Object, default: null } });
const emit = defineEmits(['close', 'saved']);

const { t } = useI18n();
const saving = ref(false);
const form = ref({ name: '', type: 'text', position: '', content: '', is_active: true });

const loadWidget = () => {
    if (props.widget) {
        form.value = { ...props.widget };
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        if (props.widget) {
            await api.put(`/admin/cms/widgets/${props.widget.id}`, form.value);
        } else {
            await api.post('/admin/cms/widgets', form.value);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save widget:', error);
        alert(t('features.widgets.messages.saveFailed'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadWidget();
});
</script>

