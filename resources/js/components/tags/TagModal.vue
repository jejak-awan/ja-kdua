<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg shadow-xl max-w-2xl w-full">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ tag ? t('features.tags.form.editTitle') : t('features.tags.form.createTitle') }}
                    </h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-muted-foreground"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.tags.form.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            @input="generateSlug"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.tags.form.namePlaceholder')"
                        >
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.tags.form.slug') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.tags.form.slugPlaceholder')"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">{{ t('features.tags.form.slugHelp') }}</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.tags.form.description') }}
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.tags.form.descriptionPlaceholder')"
                        />
                    </div>
                </form>

                <!-- Footer -->
                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                    >
                        {{ t('features.tags.form.cancel') }}
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? t('features.tags.form.saving') : (tag ? t('features.tags.form.update') : t('features.tags.form.create')) }}
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
    tag: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);

const form = ref({
    name: '',
    slug: '',
    description: '',
    image: null,
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

const loadTag = () => {
    if (props.tag) {
        form.value = {
            name: props.tag.name || '',
            slug: props.tag.slug || '',
            description: props.tag.description || '',
        };
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        if (props.tag) {
            await api.put(`/admin/cms/tags/${props.tag.id}`, form.value);
        } else {
            await api.post('/admin/cms/tags', form.value);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save tag:', error);
        alert(error.response?.data?.message || t('features.tags.form.saveError'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadTag();
});
</script>

