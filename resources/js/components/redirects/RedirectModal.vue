<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ redirect ? 'Edit Redirect' : 'Create Redirect' }}
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
                            From URL <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.from_url"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="/old-page"
                        />
                        <p class="mt-1 text-xs text-gray-500">The URL to redirect from</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            To URL <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.to_url"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="/new-page"
                        />
                        <p class="mt-1 text-xs text-gray-500">The URL to redirect to</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Status Code <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model.number="form.status_code"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option :value="301">301 - Permanent Redirect</option>
                            <option :value="302">302 - Temporary Redirect</option>
                            <option :value="307">307 - Temporary Redirect (Preserve Method)</option>
                            <option :value="308">308 - Permanent Redirect (Preserve Method)</option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
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
                        {{ saving ? 'Saving...' : (redirect ? 'Update' : 'Create') }}
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
    redirect: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);

const form = ref({
    from_url: '',
    to_url: '',
    status_code: 301,
    is_active: true,
});

const loadRedirect = () => {
    if (props.redirect) {
        form.value = {
            from_url: props.redirect.from_url || '',
            to_url: props.redirect.to_url || '',
            status_code: props.redirect.status_code || 301,
            is_active: props.redirect.is_active !== undefined ? props.redirect.is_active : true,
        };
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        if (props.redirect) {
            await api.put(`/admin/cms/redirects/${props.redirect.id}`, form.value);
        } else {
            await api.post('/admin/cms/redirects', form.value);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save redirect:', error);
        alert(error.response?.data?.message || 'Failed to save redirect');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadRedirect();
});
</script>

