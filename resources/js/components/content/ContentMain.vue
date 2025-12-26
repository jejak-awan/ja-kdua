<template>
    <div class="space-y-6">
        <!-- Title Input (Focus Style) -->
        <div class="space-y-4">
            <input
                :value="modelValue.title"
                @input="updateTitle($event.target.value)"
                type="text"
                :placeholder="$t('features.content.form.titlePlaceholder')"
                class="w-full bg-transparent text-4xl font-bold tracking-tight border-none outline-none placeholder:text-muted-foreground/40"
                autofocus
            />
            
        </div>

        <!-- Editor -->
        <TiptapEditor
            :model-value="modelValue.body"
            @update:model-value="updateField('body', $event)"
            class="min-h-[500px]"
        />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import TiptapEditor from '../TiptapEditor.vue';

const { t } = useI18n();

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue']);

// Mock Base URL (In real app, this might come from env or config)
const baseUrl = window.location.origin;

const updateField = (field, value) => {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
};

const updateTitle = (newTitle) => {
    emit('update:modelValue', { ...props.modelValue, title: newTitle });
};
</script>
