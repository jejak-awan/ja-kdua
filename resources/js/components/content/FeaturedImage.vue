<template>
    <div class="bg-card border border-border rounded-lg p-6">
        <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.content.form.featuredImage') }}</h2>
        <div class="space-y-4">
            <div v-if="modelValue" class="relative">
                <img
                    :src="modelValue"
                    alt="Featured Image"
                    class="w-full h-64 object-cover rounded-lg"
                />
                <button
                    type="button"
                    @click="$emit('update:modelValue', null)"
                    class="absolute top-2 right-2 bg-destructive text-destructive-foreground px-3 py-1 rounded-md hover:bg-destructive/90"
                >
                    {{ $t('features.content.form.remove') }}
                </button>
            </div>
            <MediaPicker
                v-else
                @selected="(media) => $emit('update:modelValue', media.url)"
                :label="$t('features.content.form.selectImage')"
            ></MediaPicker>
        </div>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import MediaPicker from '../MediaPicker.vue';

const { t } = useI18n();

defineProps({
    modelValue: {
        type: [String, null],
        default: null,
    },
});

defineEmits(['update:modelValue']);
</script>
