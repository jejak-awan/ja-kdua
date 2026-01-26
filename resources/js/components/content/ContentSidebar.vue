<template>
    <div class="space-y-4">
        <!-- Actions (Mobile/Tablet only) -->
        <div class="lg:hidden flex items-center gap-2 mb-4 px-1">
            <slot name="actions"></slot>
        </div>

        <!-- Main Sidebar Container - Unified Clean Look -->
        <div class="bg-card border border-border shadow-sm rounded-xl overflow-hidden flex flex-col">
            <!-- Publish Status Group -->
            <div class="sidebar-section">
                <button 
                    type="button"
                    @click="sections.publishing = !sections.publishing"
                    class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
                >
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-md bg-success/10 text-success">
                            <FileCheck class="w-3.5 h-3.5" />
                        </div>
                        <span class="text-sm font-semibold text-foreground">{{ $t('features.content.form.publishing') }}</span>
                    </div>
                    <ChevronDown 
                        class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                        :class="{ 'rotate-180': sections.publishing }"
                    />
                </button>
                <div v-show="sections.publishing" class="border-t border-border/5 p-5 space-y-5">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.form.status') }}</Label>
                        <Select
                            :model-value="modelValue.status"
                            @update:model-value="(val) => updateField('status', val)"
                        >
                            <SelectTrigger class="w-full h-9">
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
                            class="text-xs h-9"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.form.slug') }}</Label>
                        <div class="flex items-center gap-1 group">
                             <span class="text-[10px] text-muted-foreground font-mono select-none px-1">/</span>
                             <Input
                                :model-value="modelValue.slug"
                                @update:model-value="(val) => updateField('slug', val)"
                                class="text-xs font-mono h-9"
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
                            <SelectTrigger class="w-full h-9 capitalize">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="post">Post</SelectItem>
                                <SelectItem value="page">Page</SelectItem>
                                <SelectItem value="custom">Custom</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="flex items-center justify-between border border-border/40 rounded-lg p-3 bg-muted/20">
                        <div class="space-y-0.5">
                            <Label class="text-xs font-medium leading-none">{{ $t('features.content.form.featured') }}</Label>
                            <p class="text-[10px] text-muted-foreground leading-tight">{{ $t('features.content.form.featuredDesc') }}</p>
                        </div>
                        <Switch
                            :checked="!!modelValue.is_featured"
                            @update:checked="(val) => updateField('is_featured', val)"
                        />
                    </div>
                </div>
            </div>

            <!-- Menu Settings Group -->
            <div class="sidebar-section border-t border-border/10">
                <button 
                    type="button"
                    @click="sections.menu = !sections.menu"
                    class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
                >
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-md bg-primary/10 text-primary">
                            <MenuSquare class="w-3.5 h-3.5" />
                        </div>
                        <div class="flex-1">
                         <div class="flex items-center justify-between">
                         <span class="text-xs font-semibold text-foreground">{{ t('features.contents.form.sidebar.addToMenu') }}</span>
                         <Badge v-if="modelValue.menu_item?.add_to_menu" variant="secondary" class="h-5 text-[10px] px-1.5 bg-primary/10 text-primary border-primary/20">
                             {{ t('common.status.enabled') }}
                         </Badge>
                        </div>
                    </div>
                    <ChevronDown 
                        class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                        :class="{ 'rotate-180': sections.menu }"
                    />
                    </div>
                </button>
                <div v-show="sections.menu" class="border-t border-border/5 p-5 space-y-5">
                    <div class="flex items-center justify-between border border-border/40 rounded-lg p-3 bg-muted/20">
                        <div class="space-y-0.5">
                            <Label class="text-xs font-medium leading-none">{{ $t('features.menus.actions.addToMenu') }}</Label>
                            <p class="text-[10px] text-muted-foreground leading-tight">Add this content to a menu</p>
                        </div>
                        <Switch
                            :checked="!!modelValue.menu_item?.add_to_menu"
                            @update:checked="(val) => updateMenuField('add_to_menu', val)"
                        />
                    </div>

                    <div v-if="modelValue.menu_item?.add_to_menu" class="space-y-4 animate-in fade-in slide-in-from-top-1">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.menus.form.selectMenu') }}</Label>
                            <Select
                                :model-value="modelValue.menu_item.menu_id ? modelValue.menu_item.menu_id.toString() : ''"
                                @update:model-value="handleMenuChange"
                            >
                                <SelectTrigger class="w-full h-9">
                                    <SelectValue :placeholder="$t('features.menus.form.selectMenu')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="menu in menus" :key="menu.id" :value="menu.id.toString()">
                                        {{ menu.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.menus.form.parentItem') }}</Label>
                            <Select
                                :model-value="modelValue.menu_item.parent_id ? modelValue.menu_item.parent_id.toString() : 'root'"
                                @update:model-value="(val) => updateMenuField('parent_id', val === 'root' ? null : parseInt(val))"
                                :disabled="loadingParentItems"
                            >
                                <SelectTrigger class="w-full h-9">
                                    <SelectValue :placeholder="loadingParentItems ? 'Loading...' : $t('features.menus.form.rootItem')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="root">{{ $t('features.menus.form.rootItem') }}</SelectItem>
                                    <SelectItem v-for="item in menuParentItems" :key="item.id" :value="item.id.toString()">
                                        {{ '&nbsp;'.repeat(item.depth * 2) + (item.title || item.label) }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.menus.form.label') }}</Label>
                            <Input
                                :model-value="modelValue.menu_item.title"
                                @update:model-value="(val) => updateMenuField('title', val)"
                                class="text-xs h-9"
                                :placeholder="$t('features.menus.form.labelPlaceholder')"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Taxonomy Group -->
            <div class="sidebar-section border-t border-border/10">
                <button 
                    type="button"
                    @click="sections.taxonomy = !sections.taxonomy"
                    class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
                >
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-md bg-info/10 text-info">
                            <Tags class="w-3.5 h-3.5" />
                        </div>
                        <span class="text-sm font-semibold text-foreground">{{ $t('features.content.form.taxonomy') }}</span>
                    </div>
                    <ChevronDown 
                        class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                        :class="{ 'rotate-180': sections.taxonomy }"
                    />
                </button>
                <div v-show="sections.taxonomy" class="border-t border-border/5 p-5 space-y-5">
                    <!-- Category -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.form.category') }}</Label>
                        <Select
                            :model-value="modelValue.category_id ? modelValue.category_id.toString() : null"
                            @update:model-value="(val) => updateField('category_id', val ? parseInt(val) : null)"
                        >
                            <SelectTrigger class="w-full h-9">
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
                    <div class="space-y-2">
                        <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.form.tags') }}</Label>
                        <div class="flex flex-wrap gap-1.5 mb-2">
                            <Badge
                                v-for="tag in selectedTags"
                                :key="tag.id || tag.name"
                                variant="secondary"
                                class="pl-2 pr-1 py-0.5 gap-1 text-[10px] font-medium bg-muted/50 border-border/20"
                            >
                                {{ tag.name }}
                                <Button variant="ghost" size="icon" class="h-3 w-3 p-0 hover:text-destructive" @click="removeTag(tag.id || tag.name)">
                                    <X class="w-2.5 h-2.5" />
                                </Button>
                            </Badge>
                        </div>
                        <!-- Combobox-style tag input -->
                        <div class="relative">
                            <Input 
                                :model-value="tagInput"
                                @input="handleTagInput"
                                @keydown.enter.prevent="handleTagEnter"
                                @focus="showTagSuggestions = true"
                                @blur="handleTagBlur"
                                class="w-full text-xs h-9"
                                :placeholder="$t('features.content.form.tagInputPlaceholder')"
                            />
                            <!-- Suggestions dropdown -->
                            <div 
                                v-if="showTagSuggestions && filteredTags.length > 0"
                                class="absolute z-50 w-full mt-1 bg-background border border-border rounded-md shadow-lg max-h-[200px] overflow-y-auto"
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

            <!-- Featured Image Group -->
            <div class="sidebar-section border-t border-border/10">
                <button 
                    type="button"
                    @click="sections.image = !sections.image"
                    class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
                >
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-md bg-primary/10 text-primary">
                            <ImageIcon class="w-3.5 h-3.5" />
                        </div>
                        <span class="text-sm font-semibold text-foreground">{{ $t('features.content.form.featuredImage') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div v-if="modelValue.featured_image" class="w-6 h-6 rounded overflow-hidden border border-border/40">
                            <img :src="modelValue.featured_image" class="w-full h-full object-cover" />
                        </div>
                        <ChevronDown 
                            class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                            :class="{ 'rotate-180': sections.image }"
                        />
                    </div>
                </button>
                <div v-show="sections.image" class="border-t border-border/5 p-5">
                     <FeaturedImage v-model="modelValue.featured_image" @update:modelValue="(val) => updateField('featured_image', val)" />
                </div>
            </div>

            <!-- Excerpt Group -->
            <div class="sidebar-section border-t border-border/10">
                <button 
                    type="button"
                    @click="sections.excerpt = !sections.excerpt"
                    class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
                >
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-md bg-warning/10 text-warning">
                            <FileText class="w-3.5 h-3.5" />
                        </div>
                        <span class="text-sm font-semibold text-foreground">{{ $t('features.content.form.excerpt') }}</span>
                    </div>
                    <ChevronDown 
                        class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                        :class="{ 'rotate-180': sections.excerpt }"
                    />
                </button>
                <div v-show="sections.excerpt" class="border-t border-border/5 p-5">
                    <Textarea
                        :model-value="modelValue.excerpt"
                        @update:model-value="(val) => updateField('excerpt', val)"
                        rows="4"
                        class="resize-none text-sm bg-muted/10"
                        :placeholder="$t('features.content.form.excerptPlaceholder')"
                    />
                </div>
            </div>

            <!-- Discussion Settings Group -->
            <div class="sidebar-section border-t border-border/10">
                <button 
                    type="button"
                    @click="sections.discussion = !sections.discussion"
                    class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
                >
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-md bg-warning/10 text-warning">
                            <MessageSquare class="w-3.5 h-3.5" />
                        </div>
                        <div class="flex-1">
                         <div class="flex items-center justify-between">
                         <span class="text-xs font-semibold text-foreground">{{ t('features.contents.form.sidebar.comments') }}</span>
                         <Badge v-if="!modelValue.comment_status" variant="secondary" class="h-5 text-[10px] px-1.5 bg-warning/10 text-warning border-warning/20">
                             {{ t('common.status.disabled') }}
                         </Badge>
                        </div>
                    </div>
                    <ChevronDown 
                        class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                        :class="{ 'rotate-180': sections.discussion }"
                    />
                    </div>
                </button>
                <div v-show="sections.discussion" class="border-t border-border/5 p-5">
                    <div class="flex items-center justify-between border border-border/40 rounded-lg p-3 bg-muted/20">
                        <div class="space-y-0.5">
                            <Label class="text-xs font-medium leading-none">{{ $t('features.content.form.allowComments') }}</Label>
                            <p class="text-[10px] text-muted-foreground leading-tight">{{ $t('features.content.form.allowCommentsDesc') }}</p>
                        </div>
                        <Switch
                            :checked="!!modelValue.comment_status"
                            @update:checked="(val) => updateField('comment_status', val)"
                        />
                    </div>
                </div>
            </div>

            <!-- SEO Settings Group -->
            <div class="sidebar-section border-t border-border/10">
                <button 
                    type="button"
                    @click="sections.seo = !sections.seo"
                    class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-muted/30 transition-colors"
                >
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-md bg-destructive/10 text-destructive">
                            <Search class="w-3.5 h-3.5" />
                        </div>
                        <span class="text-sm font-semibold text-foreground">SEO</span>
                    </div>
                    <ChevronDown 
                        class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                        :class="{ 'rotate-180': sections.seo }"
                    />
                </button>
                <div v-show="sections.seo" class="border-t border-border/5 p-5 space-y-5">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.seo.metaTitle') }}</Label>
                        <Input
                            :model-value="modelValue.meta_title"
                            @update:model-value="(val) => updateField('meta_title', val)"
                             class="text-xs h-9"
                        />
                    </div>
                     <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.seo.metaDescription') }}</Label>
                        <Textarea
                            :model-value="modelValue.meta_description"
                            @update:model-value="(val) => updateField('meta_description', val)"
                            rows="3"
                             class="resize-none text-xs bg-muted/10"
                        />
                    </div>
                     <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">{{ $t('features.content.form.metaKeywords') }}</Label>
                        <Textarea
                            :model-value="modelValue.meta_keywords"
                            @update:model-value="(val) => updateField('meta_keywords', val)"
                            rows="2"
                             class="resize-none text-xs bg-muted/10"
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
                                <div v-if="modelValue.og_image" class="relative group aspect-video w-full rounded-md overflow-hidden border border-border/40 cursor-pointer" @click="open">
                                    <img :src="modelValue.og_image" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <span class="text-xs text-white font-medium">{{ $t('common.actions.change') }}</span>
                                    </div>
                                </div>
                                <Button v-else variant="outline" size="sm" class="w-full text-xs h-9" @click="open">
                                    <ImageIcon class="w-3 h-3 mr-2" /> {{ $t('features.content.form.selectOgImage') }}
                                </Button>
                             </template>
                        </MediaPicker>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
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
import { X, ChevronDown, FileCheck, Tags, FileText, Search, Image as ImageIcon, MenuSquare, MessageSquare } from 'lucide-vue-next';
import FeaturedImage from './FeaturedImage.vue';
import MediaPicker from '../MediaPicker.vue';

const { t } = useI18n();

// Collapsible section states
const sections = ref({
    publishing: true,
    menu: false,
    taxonomy: true,
    image: false,
    excerpt: false,
    seo: false,
    discussion: false
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
    },
    menus: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue', 'update:selectedTags', 'search-tags']);

// Filter available tags based on input and already selected
const availableTags = computed(() => {
    if (!Array.isArray(props.tags) || !Array.isArray(props.selectedTags)) {
        return [];
    }
    return props.tags.filter(tag => !props.selectedTags.find(st => st.id === tag.id || st.name === tag.name));
});

// Filter tags based on current input for suggestions
const filteredTags = computed(() => {
    // If searching remotely (implied by changing tags prop), we just use availableTags
    // But we can still do local prefix match highlight or simple filter
    const query = tagInput.value.trim().toLowerCase();
    
    // If no query, show partial list
    if (!query) return availableTags.value.slice(0, 10);
    
    // When searching, the parent updates 'tags' prop. 
    // We can just return availableTags because the parent should have filtered them.
    // However, for smoother UX (if network delay), we can still filter locally what we have.
    return availableTags.value.slice(0, 10);
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

const timer = ref(null);

const handleTagInput = (e) => {
    const val = e.target.value;
    tagInput.value = val;
    
    if (timer.value) clearTimeout(timer.value);
    
    timer.value = setTimeout(() => {
        emit('search-tags', val);
    }, 300);
    
    showTagSuggestions.value = true;
};

// Handle blur to close suggestions with delay (allow click on suggestion)
const handleTagBlur = () => {
    setTimeout(() => {
        showTagSuggestions.value = false;
    }, 200);
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
const menuParentItems = ref([]);
const loadingParentItems = ref(false);

const updateMenuField = (field, value) => {
    emit('update:modelValue', {
        ...props.modelValue,
        menu_item: {
            ...props.modelValue.menu_item,
            [field]: value
        }
    });
};

const handleMenuChange = (val) => {
    updateMenuField('menu_id', parseInt(val));
    fetchMenuParentItems(val);
};

const fetchMenuParentItems = async (menuId) => {
    if (!menuId) {
        menuParentItems.value = [];
        return;
    }
    
    loadingParentItems.value = true;
    try {
        const response = await api.get(`/admin/ja/menus/${menuId}/items`);
        const data = response.data?.data || response.data || [];
        // Flatten logic or just basic list? Usually API returns tree or flat.
        // Assuming flat list or need flattening. If tree, need to flatten for select.
        // Let's assume the API returns flat list or we flatten it.
        // Based on Edit.vue, it returns flatItems then builds tree.
        // We probably want a flat list with depth indicators.
        
        // Simple flatten for now if it's tree, or just use as is if flat.
        // Let's assume it returns all items.
        // We'll filter out the current item if we were editing a menu item directly, 
        // but here we are editing CONTENT which is a target. 
        // We can't select "self" as parent because "self" is a content, not a menu item yet (or is it?).
        // Actually, if we are updating an existing menu item, we shouldn't select itself as parent.
        // But the menu item ID is not known easily here unless we pass it.
        // For simplicity, just list all items. User is smart enough not to create cycles (backend should prevent too).
        
        // Let's reuse the flatten logic from Edit.vue if possible, or just a simple recursive flatten.
        // But for time, let's assume flat response or simple list.
        // Actually, `Edit.vue` builds tree. So API likely returns flat array.
        const flatItems = Array.isArray(data) ? data : [];
        
        // Calculate depth if not present
        if (flatItems.length > 0 && !flatItems[0].depth) {
             menuParentItems.value = flattenTreeForSelect(buildTree(flatItems));
        } else {
             menuParentItems.value = flatItems;
        }
    } catch (error) {
        console.error('Failed to fetch menu items:', error);
    } finally {
        loadingParentItems.value = false;
    }
};

const buildTree = (items, parentId = null) => {
    return items
        .filter(item => item.parent_id === parentId)
        .sort((a, b) => a.sort_order - b.sort_order)
        .map(item => ({
            ...item,
            children: buildTree(items, item.id)
        }));
};

const flattenTreeForSelect = (items, depth = 0) => {
    let result = [];
    items.forEach(item => {
        result.push({ ...item, depth });
        if (item.children) {
            result = result.concat(flattenTreeForSelect(item.children, depth + 1));
        }
    });
    return result;
};

// Initial load of parent items if menu_id is set
watch(() => props.modelValue.menu_item?.menu_id, (newVal) => {
    if (newVal && menuParentItems.value.length === 0) {
        fetchMenuParentItems(newVal);
    }
}, { immediate: true });

</script>
