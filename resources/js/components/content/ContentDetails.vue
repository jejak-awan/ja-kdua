<template>
    <Card class="border-none shadow-sm bg-card/50">
        <CardHeader class="pb-4">
            <CardTitle class="text-xl font-bold flex items-center gap-2">
                <FileText class="w-5 h-5 text-primary" />
                {{ $t('features.content.form.details') }}
            </CardTitle>
        </CardHeader>
        
        <CardContent class="space-y-6">
            <!-- Title -->
            <div class="space-y-1.5">
                <Label class="text-sm font-semibold tracking-tight">
                    {{ $t('features.content.form.title') }} <span class="text-destructive">*</span>
                </Label>
                <Input
                    :model-value="modelValue.title"
                    @update:model-value="(val) => updateTitle(val)"
                    type="text"
                    required
                    class="bg-background/50"
                    :placeholder="$t('features.content.form.titlePlaceholder')"
                />
            </div>

            <!-- Slug -->
            <div class="space-y-1.5">
                <Label class="text-sm font-semibold tracking-tight">
                    {{ $t('features.content.form.slug') }} <span class="text-destructive">*</span>
                </Label>
                <div class="relative">
                    <Link2 class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground/50" />
                    <Input
                        :model-value="modelValue.slug"
                        @update:model-value="(val) => updateField('slug', val)"
                        type="text"
                        required
                        class="pl-9 bg-background/50 font-mono text-xs"
                        :placeholder="$t('features.content.form.slugPlaceholder')"
                    />
                </div>
                <p class="text-[11px] text-muted-foreground/70">{{ $t('features.content.form.urlFriendlyHelp') }}</p>
            </div>

            <!-- Type & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <Label class="text-sm font-semibold tracking-tight">
                        {{ $t('features.content.form.type') }} <span class="text-destructive">*</span>
                    </Label>
                    <Select
                        :model-value="modelValue.type"
                        @update:model-value="(val) => updateField('type', val)"
                    >
                        <SelectTrigger class="bg-background/50 capitalize">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="post">Post</SelectItem>
                            <SelectItem value="page">Page</SelectItem>
                            <SelectItem value="custom">Custom</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-1.5">
                    <Label class="text-sm font-semibold tracking-tight">
                        {{ $t('features.content.form.status') }} <span class="text-destructive">*</span>
                    </Label>
                    <Select
                        :model-value="modelValue.status"
                        @update:model-value="(val) => updateField('status', val)"
                    >
                        <SelectTrigger class="bg-background/50 capitalize">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="draft">{{ $t('features.content.status.draft') }}</SelectItem>
                            <SelectItem value="published">{{ $t('features.content.status.published') }}</SelectItem>
                            <SelectItem value="archived">{{ $t('features.content.status.archived') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Category -->
            <div class="space-y-1.5">
                <Label class="text-sm font-semibold tracking-tight">
                    {{ $t('features.content.form.category') }}
                </Label>
                <Select
                    :model-value="modelValue.category_id ? modelValue.category_id.toString() : null"
                    @update:model-value="(val) => updateField('category_id', val ? parseInt(val) : null)"
                >
                    <SelectTrigger class="bg-background/50">
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
                <Label class="text-sm font-semibold tracking-tight">
                    {{ $t('features.content.form.tags') }}
                </Label>
                <div class="flex flex-wrap gap-2 p-3 rounded-lg bg-background/30 border border-border/40 min-h-[44px]">
                    <div v-if="selectedTags.length === 0" class="text-xs text-muted-foreground/50 py-1">{{ $t('features.content.form.selectTag') }}...</div>
                    <Badge
                        v-for="tag in selectedTags"
                        :key="tag.id"
                        variant="secondary"
                        class="pl-3 pr-1 py-0.5 gap-1 hover:bg-secondary/80 transition-colors"
                    >
                        {{ tag.name }}
                        <Button variant="ghost" size="icon" class="h-4 w-4 rounded-full p-0 hover:bg-destructive hover:text-destructive-foreground" @click="removeTag(tag.id)">
                            <X class="w-3 h-3" />
                        </Button>
                    </Badge>
                </div>
                <Select
                    @update:model-value="addTag"
                >
                    <SelectTrigger class="bg-background/50 mt-2">
                        <SelectValue :placeholder="$t('features.content.form.selectTag')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="tag in availableTags" :key="tag.id" :value="tag.id.toString()">
                            {{ tag.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Excerpt -->
            <div class="space-y-1.5">
                <Label class="text-sm font-semibold tracking-tight">
                    {{ $t('features.content.form.excerpt') }}
                </Label>
                <Textarea
                    :model-value="modelValue.excerpt"
                    @update:model-value="(val) => updateField('excerpt', val)"
                    rows="3"
                    class="bg-background/50 resize-none"
                    :placeholder="$t('features.content.form.excerptPlaceholder')"
                />
            </div>

            <!-- Body / Rich Text Editor -->
            <div class="space-y-1.5">
                <Label class="text-sm font-semibold tracking-tight">
                    {{ $t('features.content.form.body') }} <span class="text-destructive">*</span>
                </Label>
                <div class="rounded-lg border border-border/40 overflow-hidden shadow-inner">
                    <RichTextEditor
                        :model-value="modelValue.body"
                        @update:model-value="updateField('body', $event)"
                        class="min-h-[500px]"
                    ></RichTextEditor>
                </div>
            </div>

            <!-- Published At -->
            <div v-if="modelValue.status === 'published'" class="space-y-1.5 p-4 rounded-lg bg-primary/5 border border-primary/10 animate-in fade-in slide-in-from-top-1">
                <Label class="text-sm font-semibold tracking-tight flex items-center gap-2 text-primary">
                    <Calendar class="w-4 h-4" />
                    {{ $t('features.content.form.publishDate') }}
                </Label>
                <Input
                    :model-value="modelValue.published_at"
                    @update:model-value="(val) => updateField('published_at', val)"
                    type="datetime-local"
                    class="bg-background/50 border-primary/20"
                />
                <p class="text-[11px] text-primary/70">{{ $t('features.content.form.publishImmediatelyHelp') }}</p>
            </div>
        </CardContent>
    </Card>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import RichTextEditor from '../RichTextEditor.vue';
import Card from '@/components/ui/card.vue';
import CardHeader from '@/components/ui/card-header.vue';
import CardTitle from '@/components/ui/card-title.vue';
import CardContent from '@/components/ui/card-content.vue';
import Label from '@/components/ui/label.vue';
import Input from '@/components/ui/input.vue';
import Textarea from '@/components/ui/textarea.vue';
import Badge from '@/components/ui/badge.vue';
import Button from '@/components/ui/button.vue';
import Select from '@/components/ui/select.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';
import { 
    FileText, 
    Link2, 
    X, 
    Calendar 
} from 'lucide-vue-next';

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

const updateTitle = (newTitle) => {
    let newSlug = props.modelValue.slug;
    
    // Auto-generate slug if it's empty or matches the old title slugified
    const oldTitleSlug = slugify(props.modelValue.title || '');
    if (!newSlug || newSlug === oldTitleSlug) {
        newSlug = slugify(newTitle);
    }
    
    emit('update:modelValue', { ...props.modelValue, title: newTitle, slug: newSlug });
};

const slugify = (text) => {
    if (!text) return '';
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

const addTag = (tagIdValue) => {
    const tagId = parseInt(tagIdValue);
    if (!tagId) return;
    
    const tag = props.tags.find(t => t.id === tagId);
    if (tag && !props.selectedTags.find(st => st.id === tag.id)) {
        emit('update:selectedTags', [...props.selectedTags, tag]);
    }
};

const removeTag = (tagId) => {
    emit('update:selectedTags', props.selectedTags.filter(t => t.id !== tagId));
};
</script>
