<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ item ? t('features.menus.form.editItemTitle') : t('features.menus.form.createItemTitle') }}</h3>
                    <button @click="$emit('close')" class="text-gray-400 hover:text-muted-foreground">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ t('features.menus.form.label') }} <span class="text-red-500">*</span></label>
                        <input v-model="form.label" type="text" required class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ t('features.menus.form.type') }} <span class="text-red-500">*</span></label>
                        <select v-model="form.type" required @change="handleTypeChange" class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="link">{{ t('features.menus.form.types.link') }}</option>
                            <option value="page">{{ t('features.menus.form.types.page') }}</option>
                            <option value="category">{{ t('features.menus.form.types.category') }}</option>
                            <option value="content">{{ t('features.menus.form.types.content') }}</option>
                        </select>
                    </div>
                    <div v-if="form.type === 'link'">
                        <label class="block text-sm font-medium text-foreground mb-1">{{ t('features.menus.form.url') }} <span class="text-red-500">*</span></label>
                        <input v-model="form.url" type="url" required class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div v-else-if="form.type === 'page'">
                        <label class="block text-sm font-medium text-foreground mb-1">{{ t('features.menus.form.page') }}</label>
                        <select v-model="form.target_id" class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option :value="null">{{ t('features.menus.form.placeholders.selectPage') }}</option>
                        </select>
                    </div>
                    <div v-else-if="form.type === 'category'">
                        <label class="block text-sm font-medium text-foreground mb-1">{{ t('features.menus.form.category') }}</label>
                        <select v-model="form.target_id" class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option :value="null">{{ t('features.menus.form.placeholders.selectCategory') }}</option>
                        </select>
                    </div>
                    <div v-else-if="form.type === 'content'">
                        <label class="block text-sm font-medium text-foreground mb-1">{{ t('features.menus.form.content') }}</label>
                        <select v-model="form.target_id" class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option :value="null">{{ t('features.menus.form.placeholders.selectContent') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ t('features.menus.form.cssClasses') }}</label>
                        <input v-model="form.css_classes" type="text" class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex items-center">
                        <input v-model="form.open_in_new_tab" type="checkbox" id="new_tab" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded">
                        <label for="new_tab" class="ml-2 block text-sm text-foreground">{{ t('features.menus.form.openInNewTab') }}</label>
                    </div>
                </form>
                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button @click="$emit('close')" class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted">{{ t('features.menus.actions.cancel') }}</button>
                    <button @click="handleSubmit" :disabled="saving" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">{{ saving ? t('features.menus.actions.saving') : (item ? t('features.menus.actions.update') : t('features.menus.actions.createAction')) }}</button>
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
    item: { type: Object, default: null },
    menuId: { type: [String, Number], required: true },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);
const form = ref({
    label: '',
    type: 'link',
    url: '',
    target_id: null,
    css_classes: '',
    open_in_new_tab: false,
    parent_id: null,
});

const handleTypeChange = () => {
    form.value.url = '';
    form.value.target_id = null;
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        const payload = { ...form.value, menu_id: props.menuId };
        if (props.item) {
            await api.put(`/admin/cms/menu-items/${props.item.id}`, payload);
        } else {
            await api.post('/admin/cms/menu-items', payload);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save menu item:', error);
        alert(t('features.menus.messages.saveItemFailed'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    if (props.item) {
        form.value = { ...props.item };
    }
});
</script>

