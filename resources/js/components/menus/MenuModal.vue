<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ t('features.menus.form.createTitle') }}</h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-muted-foreground"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.menus.form.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.menus.form.placeholders.name')"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.menus.form.location') }}
                        </label>
                        <input
                            v-model="form.location"
                            type="text"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.menus.form.placeholders.location')"
                        >
                    </div>
                </form>

                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                    >
                        {{ t('features.menus.actions.cancel') }}
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? t('features.menus.actions.creating') : t('features.menus.actions.createAction') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';

const { t } = useI18n();

const emit = defineEmits(['close', 'saved']);
const router = useRouter();

const saving = ref(false);
const form = ref({
    name: '',
    location: '',
});

const handleSubmit = async () => {
    saving.value = true;
    try {
        const response = await api.post('/admin/cms/menus', form.value);
        const menu = response.data.data || response.data;
        emit('saved');
        router.push({ name: 'menus.edit', params: { id: menu.id } });
    } catch (error) {
        console.error('Failed to create menu:', error);
        alert(t('features.menus.messages.createFailed'));
    } finally {
        saving.value = false;
    }
};
</script>

