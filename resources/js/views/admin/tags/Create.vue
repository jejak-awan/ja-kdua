<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('common.actions.create') }} {{ $t('features.tags.title_singular', 'Tag') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.tags.description', 'Manage content tags') }}</p>
            </div>
            <div class="flex space-x-3">
                <router-link
                    :to="{ name: 'tags' }"
                    class="inline-flex items-center px-4 py-2 border border-input bg-card text-foreground rounded-md text-sm font-medium hover:bg-muted"
                >
                    {{ $t('common.actions.back') }}
                </router-link>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-card border border-border rounded-lg overflow-hidden">
            <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.tags.form.name') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        @input="generateSlug"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        :placeholder="$t('features.tags.form.namePlaceholder')"
                    >
                </div>

                <!-- Slug -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.tags.form.slug') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.slug"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        :placeholder="$t('features.tags.form.slugPlaceholder')"
                    >
                    <p class="mt-1 text-xs text-muted-foreground">{{ $t('features.tags.form.slugHelp') }}</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.tags.form.description') }}
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        :placeholder="$t('features.tags.form.descriptionPlaceholder')"
                    />
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-border">
                    <router-link
                        :to="{ name: 'tags' }"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-sm font-medium hover:bg-muted"
                    >
                        {{ $t('common.actions.cancel') }}
                    </router-link>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-primary-foreground bg-primary hover:bg-primary/80 disabled:opacity-50"
                    >
                        <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ saving ? $t('common.messages.loading.creating') : $t('common.actions.create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '../../../services/api';

const { t } = useI18n();
const router = useRouter();

const saving = ref(false);

const form = ref({
    name: '',
    slug: '',
    description: '',
});

const generateSlug = () => {
    if (!form.value.slug || form.value.slug === slugify(form.value.name)) {
        form.value.slug = slugify(form.value.name);
    }
};

const slugify = (text) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        await api.post('/admin/cms/tags', form.value);
        router.push({ name: 'tags' });
    } catch (error) {
        console.error('Failed to create tag:', error);
        alert(error.response?.data?.message || t('features.tags.form.saveError', 'Failed to save tag'));
    } finally {
        saving.value = false;
    }
};
</script>
