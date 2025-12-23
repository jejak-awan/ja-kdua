<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg shadow-xl max-w-2xl w-full">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ category ? t('features.categories.form.editTitle') : t('features.categories.form.createTitle') }}
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
                            {{ t('features.categories.form.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            @input="generateSlug"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.categories.form.namePlaceholder')"
                        >
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.categories.form.slug') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.categories.form.slugPlaceholder')"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">{{ t('features.categories.form.slugHelp') }}</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.categories.form.description') }}
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.categories.form.descriptionPlaceholder')"
                        />
                    </div>

                    <!-- Parent Category -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.categories.form.parent') }}
                        </label>
                        <select
                            v-model="form.parent_id"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option :value="null">{{ t('features.categories.form.noParent') }}</option>
                            <option
                                v-for="cat in availableParents"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Image -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.categories.form.image') }}
                        </label>
                        <div v-if="form.image" class="mb-2">
                            <img
                                :src="form.image"
                                alt="Category image"
                                class="w-32 h-32 object-cover rounded-lg"
                            >
                            <button
                                type="button"
                                @click="form.image = null"
                                class="mt-2 text-sm text-red-600 hover:text-red-800"
                            >
                                {{ t('features.categories.form.removeImage') }}
                            </button>
                        </div>
                        <MediaPicker
                            v-else
                            @selected="(media) => form.image = media.url"
                            :label="t('features.categories.form.selectImage')"
                        />
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.categories.form.sortOrder') }}
                        </label>
                        <input
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="0"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">{{ t('features.categories.form.sortOrderHelp') }}</p>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                        >
                        <label for="is_active" class="ml-2 block text-sm text-foreground">
                            {{ t('features.categories.form.active') }}
                        </label>
                    </div>
                </form>

                <!-- Footer -->
                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                    >
                        {{ t('features.categories.form.cancel') }}
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? t('features.categories.form.saving') : (category ? t('features.categories.form.update') : t('features.categories.form.create')) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import MediaPicker from '../MediaPicker.vue';

const { t } = useI18n();

const props = defineProps({
    category: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);
const categories = ref([]);

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
    if (!props.category) return categories.value;
    // Exclude self and descendants from parent options
    return categories.value.filter(cat => {
        if (cat.id === props.category.id) return false;
        // Check if cat is a descendant of current category
        let check = cat;
        while (check.parent_id) {
            if (check.parent_id === props.category.id) return false;
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
        categories.value = flattenTree(response.data);
    } catch (error) {
        console.error('Failed to fetch categories:', error);
    }
};

const loadCategory = () => {
    if (props.category) {
        form.value = {
            name: props.category.name || '',
            slug: props.category.slug || '',
            description: props.category.description || '',
            image: props.category.image || null,
            parent_id: props.category.parent_id || null,
            sort_order: props.category.sort_order || 0,
            is_active: props.category.is_active !== undefined ? props.category.is_active : true,
        };
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        if (props.category) {
            await api.put(`/admin/cms/categories/${props.category.id}`, form.value);
        } else {
            await api.post('/admin/cms/categories', form.value);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save category:', error);
        alert(error.response?.data?.message || t('features.categories.form.saveError'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchCategories();
    loadCategory();
});
</script>

