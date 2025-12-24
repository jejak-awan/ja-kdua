<template>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.content_templates.form.editTitle') }}</h1>
            <router-link
                :to="{ name: 'content-templates' }"
                class="text-muted-foreground hover:text-foreground"
            >
                ← {{ t('features.content_templates.form.back') }}
            </router-link>
        </div>

        <div v-if="loading && !form.name" class="text-center py-8">
            <p class="text-muted-foreground">{{ t('features.content_templates.loading') }}</p>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
            <div class="bg-card border border-border rounded-lg p-6">
                <h2 class="text-lg font-semibold text-foreground mb-4">{{ t('features.content_templates.form.details') }}</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.content_templates.form.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.content_templates.form.namePlaceholder')"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.content_templates.form.description') }}
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.content_templates.form.descriptionPlaceholder')"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.content_templates.form.type') }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.type"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="post">Post</option>
                            <option value="page">Page</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-card border border-border rounded-lg p-6">
                <h2 class="text-lg font-semibold text-foreground mb-4">{{ t('features.content_templates.form.content') }}</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.content_templates.form.titleLabel') }}
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.content_templates.form.titlePlaceholder')"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.content_templates.form.body') }}
                        </label>
                        <RichTextEditor
                            v-model="form.body"
                            :placeholder="t('features.content_templates.form.bodyPlaceholder')"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.content_templates.form.excerpt') }}
                        </label>
                        <textarea
                            v-model="form.excerpt"
                            rows="3"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.content_templates.form.excerptPlaceholder')"
                        />
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <router-link
                    :to="{ name: 'content-templates' }"
                    class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                >
                    {{ t('features.content_templates.form.cancel') }}
                </router-link>
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50"
                >
                    {{ saving ? t('features.content_templates.form.updating') : t('features.content_templates.form.update') }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseSingleResponse } from '../../../utils/responseParser';
import RichTextEditor from '../../../components/RichTextEditor.vue';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const templateId = route.params.id;

const loading = ref(false);
const saving = ref(false);

const form = ref({
    name: '',
    description: '',
    type: 'post',
    title: '',
    body: '',
    excerpt: '',
});

const fetchTemplate = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/content-templates/${templateId}`);
        const template = parseSingleResponse(response) || {};
        
        form.value = {
            name: template.name || '',
            description: template.description || '',
            type: template.type || 'post',
            title: template.title || '',
            body: template.body || '',
            excerpt: template.excerpt || '',
        };
    } catch (error) {
        console.error('Failed to fetch template:', error);
        alert(t('features.content_templates.messages.loadError'));
        router.push({ name: 'content-templates' });
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        await api.put(`/admin/cms/content-templates/${templateId}`, form.value);
        router.push({ name: 'content-templates' });
    } catch (error) {
        console.error('Failed to update template:', error);
        alert(error.response?.data?.message || t('features.content_templates.messages.updateError'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchTemplate();
});
</script>

