<template>
    <div class="bg-card border border-border rounded-lg p-6">
        <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.content.form.seoSettings') }}</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-foreground mb-1">
                    {{ $t('features.content.form.metaTitle') }}
                </label>
                <input
                    :value="modelValue.meta_title"
                    @input="$emit('update:modelValue', { ...modelValue, meta_title: $event.target.value })"
                    type="text"
                    maxlength="255"
                    class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="SEO title (defaults to content title)"
                />
                <p class="mt-1 text-xs text-muted-foreground">{{ modelValue.meta_title?.length || 0 }}/255 characters</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-foreground mb-1">
                    {{ $t('features.content.form.metaDescription') }}
                </label>
                <textarea
                    :value="modelValue.meta_description"
                    @input="$emit('update:modelValue', { ...modelValue, meta_description: $event.target.value })"
                    rows="3"
                    maxlength="500"
                    class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="SEO description (defaults to excerpt)"
                ></textarea>
                <p class="mt-1 text-xs text-muted-foreground">{{ modelValue.meta_description?.length || 0 }}/500 characters</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-foreground mb-1">
                    {{ $t('features.content.form.metaKeywords') }}
                </label>
                <input
                    :value="modelValue.meta_keywords"
                    @input="$emit('update:modelValue', { ...modelValue, meta_keywords: $event.target.value })"
                    type="text"
                    class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    :placeholder="$t('features.content.form.keywordsPlaceholder')"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-foreground mb-1">
                    {{ $t('features.content.form.ogImage') }}
                </label>
                <div v-if="modelValue.og_image" class="relative mb-2">
                    <img
                        :src="modelValue.og_image"
                        alt="OG Image"
                        class="w-full h-48 object-cover rounded-lg"
                    />
                    <button
                        type="button"
                        @click="$emit('update:modelValue', { ...modelValue, og_image: null })"
                        class="absolute top-2 right-2 bg-destructive text-destructive-foreground px-3 py-1 rounded-md hover:bg-destructive/90"
                    >
                        {{ $t('features.content.form.remove') }}
                    </button>
                </div>
                <MediaPicker
                    v-else
                    @selected="(media) => $emit('update:modelValue', { ...modelValue, og_image: media.url })"
                    label="Select OG Image"
                ></MediaPicker>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import MediaPicker from '../MediaPicker.vue';

const { t } = useI18n();

defineProps({
    modelValue: {
        type: Object,
        required: true,
        default: () => ({
            meta_title: '',
            meta_description: '',
            meta_keywords: '',
            og_image: null,
        }),
    },
});

defineEmits(['update:modelValue']);
</script>
