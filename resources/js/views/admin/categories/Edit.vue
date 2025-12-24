<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('common.actions.edit') }} {{ $t('features.categories.title_singular') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.categories.description') }}</p>
            </div>
            <div class="flex space-x-3">
                <router-link
                    :to="{ name: 'categories' }"
                    class="inline-flex items-center px-4 py-2 border border-input bg-card text-foreground rounded-md text-sm font-medium hover:bg-muted"
                >
                    {{ $t('common.actions.back') }}
                </router-link>
            </div>
        </div>

        <!-- Form -->
        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ $t('common.messages.loading.default') }}</p>
        </div>

        <div v-else class="bg-card border border-border rounded-lg overflow-hidden">
            <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.categories.form.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            @input="generateSlug"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.categories.form.namePlaceholder')"
                        >
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.categories.form.slug') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.categories.form.slugPlaceholder')"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">{{ $t('features.categories.form.slugHelp') }}</p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.categories.form.description') }}
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        :placeholder="$t('features.categories.form.descriptionPlaceholder')"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Parent Category -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.categories.form.parent') }}
                        </label>
                        <select
                            v-model="form.parent_id"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option :value="null">{{ $t('features.categories.form.noParent') }}</option>
                            <option
                                v-for="cat in availableParents"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.categories.form.sortOrder') }}
                        </label>
                        <input
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="0"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">{{ $t('features.categories.form.sortOrderHelp') }}</p>
                    </div>
                </div>

                <!-- Image -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.categories.form.image') }}
                    </label>
                    <div v-if="form.image" class="mb-2 relative w-32 h-32">
                        <img
                            :src="form.image"
                            alt="Category image"
                            class="w-full h-full object-cover rounded-lg border border-border"
                        >
                        <button
                            type="button"
                            @click="form.image = null"
                            class="absolute -top-2 -right-2 bg-destructive text-destructive-foreground rounded-full p-1 hover:bg-destructive/90 shadow-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <MediaPicker
                        v-else
                        @selected="(media) => form.image = media.url"
                        :label="$t('features.categories.form.selectImage')"
                    />
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        id="is_active"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded bg-transparent"
                    >
                    <label for="is_active" class="ml-2 block text-sm text-foreground">
                        {{ $t('features.categories.form.active') }}
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-border">
                    <router-link
                        :to="{ name: 'categories' }"
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
                        {{ saving ? $t('common.messages.loading.saving') : $t('common.actions.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import api from '../../../services/api';
import MediaPicker from '../../../components/MediaPicker.vue';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();

const loading = ref(true);
const saving = ref(false);
const categories = ref([]);
const currentCategory = ref(null);

const form = ref({
    name: '',
    slug: '',
    description: '',
    image: null,
    parent_id: null,
    sort_order: 0,
    is_active: true,
});

const availableParents = computed(() => {
    if (!currentCategory.value) return categories.value;
    // Exclude self and descendants from parent options
    return categories.value.filter(cat => {
        if (cat.id === currentCategory.value.id) return false;
        // Check if cat is a descendant of current category
        let check = cat;
        while (check.parent_id) {
            if (check.parent_id === currentCategory.value.id) return false;
            check = categories.value.find(c => c.id === check.parent_id);
            if (!check) break;
        }
        return true;
    });
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

const fetchCategories = async () => {
    try {
        const response = await api.get('/admin/cms/categories?tree=true');
        // Flatten tree for parent selection
        const flattenTree = (items) => {
            let result = [];
            items.forEach(item => {
                result.push(item);
                if (item.children && item.children.length > 0) {
                    result = result.concat(flattenTree(item.children));
                }
            });
            return result;
        };
        categories.value = flattenTree(response.data?.data || response.data || []);
    } catch (error) {
        console.error('Failed to fetch categories:', error);
    }
};

const fetchCategory = async () => {
    try {
        const response = await api.get(`/admin/cms/categories/${route.params.id}`);
        const data = response.data?.data || response.data;
        currentCategory.value = data;
        form.value = {
            name: data.name || '',
            slug: data.slug || '',
            description: data.description || '',
            image: data.image || null,
            parent_id: data.parent_id || null,
            sort_order: data.sort_order || 0,
            is_active: data.is_active !== undefined ? data.is_active : true,
        };
    } catch (error) {
        console.error('Failed to fetch category:', error);
        alert(t('common.messages.error.fetchFailed'));
        router.push({ name: 'categories' });
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        await api.put(`/admin/cms/categories/${route.params.id}`, form.value);
        router.push({ name: 'categories' });
    } catch (error) {
        console.error('Failed to update category:', error);
        alert(error.response?.data?.message || t('features.categories.form.saveError'));
    } finally {
        saving.value = false;
    }
};

onMounted(async () => {
    loading.value = true;
    await Promise.all([fetchCategories(), fetchCategory()]);
    loading.value = false;
});
</script>
