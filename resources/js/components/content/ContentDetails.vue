<template>
    <div class="bg-card border border-border rounded-lg p-6">
        <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.content.form.details') }}</h2>
        
        <div class="space-y-4">
            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-1">
                    {{ $t('features.content.form.title') }} <span class="text-destructive">*</span>
                </label>
                <input
                    :value="modelValue.title"
                    @input="updateTitle"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    :placeholder="$t('features.content.form.titlePlaceholder')"
                />
            </div>

            <!-- Slug -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-1">
                    {{ $t('features.content.form.slug') }} <span class="text-destructive">*</span>
                </label>
                <input
                    :value="modelValue.slug"
                    @input="updateField('slug', $event.target.value)"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    :placeholder="$t('features.content.form.slugPlaceholder')"
                />
                <p class="mt-1 text-xs text-muted-foreground">{{ $t('features.content.form.urlFriendlyHelp') }}</p>
            </div>

            <!-- Type & Status -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.content.form.type') }} <span class="text-destructive">*</span>
                    </label>
                    <select
                        :value="modelValue.type"
                        @change="updateField('type', $event.target.value)"
                        required
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="post">Post</option>
                        <option value="page">Page</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.content.form.status') }} <span class="text-destructive">*</span>
                    </label>
                    <select
                        :value="modelValue.status"
                        @change="updateField('status', $event.target.value)"
                        required
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="draft">{{ $t('features.content.status.draft') }}</option>
                        <option value="published">{{ $t('features.content.status.published') }}</option>
                        <option value="archived">{{ $t('features.content.status.archived') }}</option>
                    </select>
                </div>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-1">
                    {{ $t('features.content.form.category') }}
                </label>
                <select
                    :value="modelValue.category_id"
                    @change="updateField('category_id', $event.target.value)"
                    class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                >
                    <option :value="null">{{ $t('features.content.form.selectCategory') }}</option>
                    <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="category.id"
                    >
                        {{ category.name }}
                    </option>
                </select>
            </div>

            <!-- Tags -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-1">
                    {{ $t('features.content.form.tags') }}
                </label>
                <div class="flex flex-wrap gap-2 mb-2">
                    <span
                        v-for="tag in selectedTags"
                        :key="tag.id"
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-secondary text-secondary-foreground"
                    >
                        {{ tag.name }}
                        <button
                            type="button"
                            @click="removeTag(tag.id)"
                            class="ml-2 text-muted-foreground hover:text-foreground"
                        >
                            ×
                        </button>
                    </span>
                </div>
                <select
                    @change="addTag"
                    class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                >
                    <option value="">{{ $t('features.content.form.selectTag') }}</option>
                    <option
                        v-for="tag in availableTags"
                        :key="tag.id"
                        :value="tag.id"
                    >
                        {{ tag.name }}
                    </option>
                </select>
            </div>

            <!-- Excerpt -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-1">
                    {{ $t('features.content.form.excerpt') }}
                </label>
                <textarea
                    :value="modelValue.excerpt"
                    @input="updateField('excerpt', $event.target.value)"
                    rows="3"
                    class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    :placeholder="$t('features.content.form.excerptPlaceholder')"
                ></textarea>
            </div>

            <!-- Body / Rich Text Editor -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-1">
                    {{ $t('features.content.form.body') }} <span class="text-destructive">*</span>
                </label>
                <RichTextEditor
                    :model-value="modelValue.body"
                    @update:model-value="updateField('body', $event)"
                    class="min-h-[400px]"
                ></RichTextEditor>
            </div>

            <!-- Published At -->
            <div v-if="modelValue.status === 'published'">
                <label class="block text-sm font-medium text-foreground mb-1">
                    {{ $t('features.content.form.publishDate') }}
                </label>
                <input
                    :value="modelValue.published_at"
                    @input="updateField('published_at', $event.target.value)"
                    type="datetime-local"
                    class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                />
                <p class="mt-1 text-xs text-muted-foreground">{{ $t('features.content.form.publishImmediatelyHelp') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import RichTextEditor from '../RichTextEditor.vue';

const { t } = useI18n();

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        default: () => []
    },
    tags: {
        type: Array,
        default: () => []
    },
    selectedTags: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue', 'update:selectedTags']);

const availableTags = computed(() => {
    if (!Array.isArray(props.tags) || !Array.isArray(props.selectedTags)) {
        return [];
    }
    return props.tags.filter(tag => !props.selectedTags.find(st => st.id === tag.id));
});

const updateField = (field, value) => {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
};

const updateTitle = (event) => {
    const newTitle = event.target.value;
    let newSlug = props.modelValue.slug;
    
    // Auto-generate slug if it's empty or matches the old title slugified
    const oldTitleSlug = slugify(props.modelValue.title || '');
    if (!newSlug || newSlug === oldTitleSlug) {
        newSlug = slugify(newTitle);
    }
    
    emit('update:modelValue', { ...props.modelValue, title: newTitle, slug: newSlug });
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

const addTag = (event) => {
    const tagId = parseInt(event.target.value);
    if (!tagId) return;
    
    const tag = props.tags.find(t => t.id === tagId);
    if (tag && !props.selectedTags.find(st => st.id === tag.id)) {
        emit('update:selectedTags', [...props.selectedTags, tag]);
    }
    event.target.value = '';
};

const removeTag = (tagId) => {
    emit('update:selectedTags', props.selectedTags.filter(t => t.id !== tagId));
};
</script>
