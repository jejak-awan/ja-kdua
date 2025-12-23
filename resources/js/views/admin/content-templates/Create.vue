<template>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.content_templates.form.createTitle') }}</h1>
            <router-link
                :to="{ name: 'content-templates' }"
                class="text-muted-foreground hover:text-foreground"
            >
                ← {{ t('features.content_templates.form.back') }}
            </router-link>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <div class="bg-card shadow rounded-lg p-6">
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

            <div class="bg-card shadow rounded-lg p-6">
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
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ saving ? t('features.content_templates.form.saving') : t('features.content_templates.form.save') }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import RichTextEditor from '../../../components/RichTextEditor.vue';

const { t } = useI18n();
const router = useRouter();
const saving = ref(false);

const form = ref({
    name: '',
    description: '',
    type: 'post',
    title: '',
    body: '',
    excerpt: '',
});

const handleSubmit = async () => {
    saving.value = true;
    try {
        await api.post('/admin/cms/content-templates', form.value);
        router.push({ name: 'content-templates' });
    } catch (error) {
        console.error('Failed to create template:', error);
        alert(error.response?.data?.message || t('features.content_templates.messages.saveError'));
    } finally {
        saving.value = false;
    }
};
</script>

