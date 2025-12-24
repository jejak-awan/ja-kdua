<template>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">Create Email Template</h1>
            <router-link
                :to="{ name: 'email-templates' }"
                class="text-muted-foreground hover:text-foreground"
            >
                ← Back to Templates
            </router-link>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <div class="bg-card border border-border rounded-lg p-6">
                <h2 class="text-lg font-semibold text-foreground mb-4">Template Details</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Template name"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Subject <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.subject"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Email subject"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Type
                        </label>
                        <select
                            v-model="form.type"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="custom">Custom</option>
                            <option value="notification">Notification</option>
                            <option value="transactional">Transactional</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-foreground">Template Content</h2>
                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            @click="showVariables = !showVariables"
                            class="px-3 py-1 text-sm border border-input bg-card text-foreground rounded-md hover:bg-muted"
                        >
                            {{ showVariables ? 'Hide' : 'Show' }} Variables
                        </button>
                        <button
                            type="button"
                            @click="handlePreview"
                            class="px-3 py-1 text-sm border border-input bg-card text-foreground rounded-md hover:bg-muted"
                        >
                            Preview
                        </button>
                    </div>
                </div>

                <div v-if="showVariables" class="mb-4 p-4 bg-muted rounded-lg">
                    <h3 class="text-sm font-medium text-foreground mb-2">Available Variables:</h3>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div v-for="variable in variables" :key="variable" class="flex items-center">
                            <code class="px-2 py-1 bg-card rounded">{{ variable }}</code>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-muted-foreground">Use <code>{{ '{' }}{{ '{' }} variable_name {{ '}' }}{{ '}' }}</code> in your template</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        HTML Content <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="form.body"
                        rows="20"
                        required
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                        placeholder="Enter HTML template content..."
                    />
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <router-link
                    :to="{ name: 'email-templates' }"
                    class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                >
                    Cancel
                </router-link>
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50"
                >
                    {{ saving ? 'Creating...' : 'Create Template' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../../services/api';
import { parseSingleResponse } from '../../../utils/responseParser';

const router = useRouter();
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

const handlePreview = async () => {
    try {
        const response = await api.post('/admin/cms/email-templates/preview', form.value);
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

const handleSubmit = async () => {
    saving.value = true;
    try {
        const response = await api.post('/admin/cms/email-templates', form.value);
        router.push({ name: 'email-templates' });
    } catch (error) {
        console.error('Failed to create template:', error);
        alert(error.response?.data?.message || 'Failed to create template');
    } finally {
        saving.value = false;
    }
};
</script>

