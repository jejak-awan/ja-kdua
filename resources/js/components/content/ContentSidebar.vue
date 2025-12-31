<template>
    <div class="space-y-3">
        <!-- Actions (Mobile/Tablet only - usually sticky on desktop) -->
        <div class="lg:hidden flex items-center gap-2 mb-4">
            <slot name="actions"></slot>
        </div>

        <!-- Publish Status -->
        <div class="bg-card border border-border rounded-lg overflow-hidden">
            <button 
                type="button"
                @click="sections.publishing = !sections.publishing"
                class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-muted/50 transition-colors"
            >
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-md bg-emerald-500/10 text-emerald-500">
                        <FileCheck class="w-3.5 h-3.5" />
                    </div>
                    <span class="text-sm font-semibold text-foreground">{{ $t('features.content.form.publishing') }}</span>
                </div>
                <ChevronDown 
                    class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                    :class="{ 'rotate-180': sections.publishing }"
                />
            </button>
            <div v-show="sections.publishing" class="border-t border-border p-4 space-y-4">
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.form.status') }}</Label>
                    <Select
                        :model-value="modelValue.status"
                        @update:model-value="(val) => updateField('status', val)"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="draft">{{ $t('features.content.status.draft') }}</SelectItem>
                            <SelectItem value="published">{{ $t('features.content.status.published') }}</SelectItem>
                            <SelectItem value="archived">{{ $t('features.content.status.archived') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div v-if="modelValue.status === 'published'" class="space-y-1.5 animate-in fade-in slide-in-from-top-1">
                    <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.form.publishDate') }}</Label>
                    <Input
                        :model-value="modelValue.published_at"
                        @update:model-value="(val) => updateField('published_at', val)"
                        type="datetime-local"
                        class="text-xs"
                    />
                </div>

                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.form.slug') }}</Label>
                    <div class="flex items-center gap-1">
                         <span class="text-[10px] text-muted-foreground font-mono select-none">/</span>
                         <Input
                            :model-value="modelValue.slug"
                            @update:model-value="(val) => updateField('slug', val)"
                            class="text-xs font-mono"
                            :placeholder="$t('features.content.form.slugPlaceholder')"
                        />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.form.type') }}</Label>
                     <Select
                        :model-value="modelValue.type"
                        @update:model-value="(val) => updateField('type', val)"
                    >
                        <SelectTrigger class="w-full capitalize">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="post">Post</SelectItem>
                            <SelectItem value="page">Page</SelectItem>
                            <SelectItem value="custom">Custom</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="flex items-center justify-between border rounded-md p-3">
                    <div class="space-y-0.5">
                        <Label class="text-xs font-medium">{{ $t('features.content.form.featured') }}</Label>
                        <p class="text-[10px] text-muted-foreground">{{ $t('features.content.form.featuredDesc') }}</p>
                    </div>
                    <Switch
                        :checked="!!modelValue.is_featured"
                        @update:checked="(val) => updateField('is_featured', val)"
                    />
                </div>
            </div>
        </div>

        <!-- Taxonomy -->
        <div class="bg-card border border-border rounded-lg overflow-hidden">
            <button 
                type="button"
                @click="sections.taxonomy = !sections.taxonomy"
                class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-muted/50 transition-colors"
            >
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-md bg-blue-500/10 text-blue-500">
                        <Tags class="w-3.5 h-3.5" />
                    </div>
                    <span class="text-sm font-semibold text-foreground">{{ $t('features.content.form.taxonomy') }}</span>
                </div>
                <ChevronDown 
                    class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                    :class="{ 'rotate-180': sections.taxonomy }"
                />
            </button>
            <div v-show="sections.taxonomy" class="border-t border-border p-4 space-y-4">
                <!-- Category -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.form.category') }}</Label>
                    <Select
                        :model-value="modelValue.category_id ? modelValue.category_id.toString() : null"
                        @update:model-value="(val) => updateField('category_id', val ? parseInt(val) : null)"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue :placeholder="$t('features.content.form.selectCategory')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="category in categories" :key="category.id" :value="category.id.toString()">
                                {{ category.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Tags -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.form.tags') }}</Label>
                    <div class="flex flex-wrap gap-2 mb-2">
                        <Badge
                            v-for="tag in selectedTags"
                            :key="tag.id || tag.name"
                            variant="secondary"
                            class="pl-2 pr-1 py-0.5 gap-1 text-xs"
                        >
                            {{ tag.name }}
                            <Button variant="ghost" size="icon" class="h-3 w-3 p-0 hover:text-destructive" @click="removeTag(tag.id || tag.name)">
                                <X class="w-2 h-2" />
                            </Button>
                        </Badge>
                    </div>
                    <!-- Combobox-style tag input -->
                    <div class="relative">
                        <Input 
                            v-model="tagInput"
                            @keydown.enter.prevent="handleTagEnter"
                            @focus="showTagSuggestions = true"
                            @blur="handleTagBlur"
                            class="w-full text-xs"
                            :placeholder="$t('features.content.form.tagInputPlaceholder')"
                        />
                        <!-- Suggestions dropdown -->
                        <div 
                            v-if="showTagSuggestions && filteredTags.length > 0"
                            class="absolute z-50 w-full mt-1 bg-popover border border-border rounded-md shadow-lg max-h-[200px] overflow-y-auto"
                        >
                            <div
                                v-for="tag in filteredTags"
                                :key="tag.id"
                                @mousedown.prevent="selectTag(tag)"
                                class="px-3 py-2 text-xs cursor-pointer hover:bg-accent hover:text-accent-foreground transition-colors"
                            >
                                {{ tag.name }}
                            </div>
                        </div>
                    </div>
                    <p class="text-[10px] text-muted-foreground/60 italic">{{ $t('features.content.form.tagInputHint') }}</p>
                </div>
            </div>
        </div>

        <!-- Featured Image -->
        <div class="bg-card border border-border rounded-lg overflow-hidden">
            <button 
                type="button"
                @click="sections.image = !sections.image"
                class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-muted/50 transition-colors"
            >
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-md bg-purple-500/10 text-purple-500">
                        <ImageIcon class="w-3.5 h-3.5" />
                    </div>
                    <span class="text-sm font-semibold text-foreground">{{ $t('features.content.form.featuredImage') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div v-if="modelValue.featured_image" class="w-6 h-6 rounded overflow-hidden border border-border">
                        <img :src="modelValue.featured_image" class="w-full h-full object-cover" />
                    </div>
                    <ChevronDown 
                        class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                        :class="{ 'rotate-180': sections.image }"
                    />
                </div>
            </button>
            <div v-show="sections.image" class="border-t border-border p-4">
                 <FeaturedImage v-model="modelValue.featured_image" @update:modelValue="(val) => updateField('featured_image', val)" />
            </div>
        </div>

        <!-- Excerpt -->
        <div class="bg-card border border-border rounded-lg overflow-hidden">
            <button 
                type="button"
                @click="sections.excerpt = !sections.excerpt"
                class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-muted/50 transition-colors"
            >
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-md bg-amber-500/10 text-amber-500">
                        <FileText class="w-3.5 h-3.5" />
                    </div>
                    <span class="text-sm font-semibold text-foreground">{{ $t('features.content.form.excerpt') }}</span>
                </div>
                <ChevronDown 
                    class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                    :class="{ 'rotate-180': sections.excerpt }"
                />
            </button>
            <div v-show="sections.excerpt" class="border-t border-border p-4">
                <Textarea
                    :model-value="modelValue.excerpt"
                    @update:model-value="(val) => updateField('excerpt', val)"
                    rows="4"
                    class="resize-none text-sm"
                    :placeholder="$t('features.content.form.excerptPlaceholder')"
                />
            </div>
        </div>

        <!-- SEO Settings -->
        <div class="bg-card border border-border rounded-lg overflow-hidden">
            <button 
                type="button"
                @click="sections.seo = !sections.seo"
                class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-muted/50 transition-colors"
            >
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-md bg-red-500/10 text-red-500">
                        <Search class="w-3.5 h-3.5" />
                    </div>
                    <span class="text-sm font-semibold text-foreground">SEO</span>
                </div>
                <ChevronDown 
                    class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                    :class="{ 'rotate-180': sections.seo }"
                />
            </button>
            <div v-show="sections.seo" class="border-t border-border p-4 space-y-4">
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.seo.metaTitle') }}</Label>
                    <Input
                        :model-value="modelValue.meta_title"
                        @update:model-value="(val) => updateField('meta_title', val)"
                         class="text-xs"
                    />
                </div>
                 <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.seo.metaDescription') }}</Label>
                    <Textarea
                        :model-value="modelValue.meta_description"
                        @update:model-value="(val) => updateField('meta_description', val)"
                        rows="3"
                         class="resize-none text-xs"
                    />
                </div>
                 <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.form.metaKeywords') }}</Label>
                    <Textarea
                        :model-value="modelValue.meta_keywords"
                        @update:model-value="(val) => updateField('meta_keywords', val)"
                        rows="2"
                         class="resize-none text-xs"
                         :placeholder="$t('features.content.form.keywordsPlaceholder')"
                    />
                </div>
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.form.ogImage') }}</Label>
                    <MediaPicker
                        @selected="(media) => updateField('og_image', media?.url || media)"
                        :label="$t('features.content.form.selectOgImage')"
                        :constraints="{
                            allowedExtensions: ['jpg', 'jpeg', 'png', 'webp'],
                            minWidth: 1200,
                            minHeight: 630
                        }"
                    >
                         <template #trigger="{ open }">
                            <div v-if="modelValue.og_image" class="relative group aspect-video w-full rounded-md overflow-hidden border border-border cursor-pointer" @click="open">
                                <img :src="modelValue.og_image" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="text-xs text-white font-medium">{{ $t('common.actions.change') }}</span>
                                </div>
                            </div>
                            <Button v-else variant="outline" size="sm" class="w-full text-xs" @click="open">
                                <ImageIcon class="w-3 h-3 mr-2" /> {{ $t('features.content.form.selectOgImage') }}
                            </Button>
                         </template>
                    </MediaPicker>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Label from '@/components/ui/label.vue';
import Input from '@/components/ui/input.vue';
import Textarea from '@/components/ui/textarea.vue';
import Select from '@/components/ui/select.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';
import Badge from '@/components/ui/badge.vue';
import Button from '@/components/ui/button.vue';
import Switch from '@/components/ui/switch.vue';
import { X, ChevronDown, FileCheck, Tags, FileText, Search, Image as ImageIcon } from 'lucide-vue-next';
import FeaturedImage from './FeaturedImage.vue';
import MediaPicker from '../MediaPicker.vue';

const { t } = useI18n();

// Collapsible section states
const sections = ref({
    publishing: true,
    taxonomy: true,
    image: false,
    excerpt: false,
    seo: false
});

// Tag input state
const tagInput = ref('');
const showTagSuggestions = ref(false);

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

// Filter available tags based on input and already selected
const availableTags = computed(() => {
    if (!Array.isArray(props.tags) || !Array.isArray(props.selectedTags)) {
        return [];
    }
    return props.tags.filter(tag => !props.selectedTags.find(st => st.id === tag.id || st.name === tag.name));
});

// Filter tags based on current input for suggestions
const filteredTags = computed(() => {
    const query = tagInput.value.trim().toLowerCase();
    if (!query) return availableTags.value.slice(0, 10); // Show first 10 when empty
    return availableTags.value.filter(tag => 
        tag.name.toLowerCase().includes(query)
    ).slice(0, 10);
});

const updateField = (field, value) => {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
};

// Handle Enter key to add tag (existing or new)
const handleTagEnter = () => {
    const name = tagInput.value.trim();
    if (!name) return;
    
    // Check if tag already selected
    if (props.selectedTags.find(st => st.name.toLowerCase() === name.toLowerCase())) {
        tagInput.value = '';
        return;
    }
    
    // Check if existing tag matches
    const existingTag = props.tags.find(t => t.name.toLowerCase() === name.toLowerCase());
    if (existingTag) {
        emit('update:selectedTags', [...props.selectedTags, existingTag]);
    } else {
        // Create new tag (temporary, will be created on backend during save)
        const newTag = { id: null, name: name, isNew: true };
        emit('update:selectedTags', [...props.selectedTags, newTag]);
    }
    
    tagInput.value = '';
    showTagSuggestions.value = false;
};

// Handle blur to close suggestions with delay (allow click on suggestion)
const handleTagBlur = () => {
    setTimeout(() => {
        showTagSuggestions.value = false;
    }, 150);
};

// Select tag from suggestions
const selectTag = (tag) => {
    if (!props.selectedTags.find(st => st.id === tag.id)) {
        emit('update:selectedTags', [...props.selectedTags, tag]);
    }
    tagInput.value = '';
    showTagSuggestions.value = false;
};

// Remove tag (support both id and name for new tags)
const removeTag = (tagIdOrName) => {
    emit('update:selectedTags', props.selectedTags.filter(t => 
        t.id ? t.id !== tagIdOrName : t.name !== tagIdOrName
    ));
};
</script>
