<template>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Edit Email Template</h1>
            <router-link
                :to="{ name: 'email-templates' }"
                class="text-gray-600 hover:text-gray-900"
            >
                ← Back to Templates
            </router-link>
        </div>

        <div v-if="loading && !form.name" class="text-center py-8">
            <p class="text-gray-500">Loading template...</p>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Template Details</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Template name"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Subject <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.subject"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Email subject"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Type
                        </label>
                        <select
                            v-model="form.type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="custom">Custom</option>
                            <option value="notification">Notification</option>
                            <option value="transactional">Transactional</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Template Content</h2>
                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            @click="showVariables = !showVariables"
                            class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            {{ showVariables ? 'Hide' : 'Show' }} Variables
                        </button>
                        <button
                            type="button"
                            @click="handlePreview"
                            class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Preview
                        </button>
                        <button
                            type="button"
                            @click="handleSendTest"
                            class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Send Test
                        </button>
                    </div>
                </div>

                <div v-if="showVariables" class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Available Variables:</h3>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div v-for="variable in variables" :key="variable" class="flex items-center">
                            <code class="px-2 py-1 bg-white rounded">{{ variable }}</code>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Use <code>{{ '{' }}{{ '{' }} variable_name {{ '}' }}{{ '}' }}</code> in your template</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        HTML Content <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="form.body"
                        rows="20"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                        placeholder="Enter HTML template content..."
                    />
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <router-link
                    :to="{ name: 'email-templates' }"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cancel
                </router-link>
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ saving ? 'Updating...' : 'Update Template' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../../services/api';
import { parseSingleResponse } from '../../../utils/responseParser';

const route = useRoute();
const router = useRouter();
const templateId = route.params.id;

const loading = ref(false);
const saving = ref(false);
const showVariables = ref(false);

const form = ref({
    name: '',
    subject: '',
    type: 'custom',
    body: '',
});

const variables = ref([
    'user_name',
    'user_email',
    'site_name',
    'site_url',
    'current_date',
    'current_time',
]);

const fetchTemplate = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/email-templates/${templateId}`);
        const template = parseSingleResponse(response) || {};
        
        form.value = {
            name: template.name || '',
            subject: template.subject || '',
            type: template.type || 'custom',
            body: template.body || '',
        };
    } catch (error) {
        console.error('Failed to fetch template:', error);
        alert('Failed to load template');
        router.push({ name: 'email-templates' });
    } finally {
        loading.value = false;
    }
};

const handlePreview = async () => {
    try {
        const response = await api.post(`/admin/cms/email-templates/${templateId}/preview`);
        const data = parseSingleResponse(response) || {};
        const previewWindow = window.open('', '_blank');
        if (previewWindow) {
            previewWindow.document.write(data.html || '');
        }
    } catch (error) {
        console.error('Failed to preview template:', error);
        alert('Failed to preview template');
    }
};

const handleSendTest = async () => {
    const email = prompt('Enter email address to send test email:');
    if (!email) return;

    try {
        await api.post(`/admin/cms/email-templates/${templateId}/send-test`, { email });
        alert('Test email sent successfully');
    } catch (error) {
        console.error('Failed to send test email:', error);
        alert(error.response?.data?.message || 'Failed to send test email');
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        await api.put(`/admin/cms/email-templates/${templateId}`, form.value);
        router.push({ name: 'email-templates' });
    } catch (error) {
        console.error('Failed to update template:', error);
        alert(error.response?.data?.message || 'Failed to update template');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchTemplate();
});
</script>

