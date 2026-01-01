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

        <!-- Editor Mode Toggle -->
        <div class="flex items-center gap-1 p-1 bg-muted rounded-lg w-fit">
            <Button 
                v-for="mode in ['classic', 'builder']" 
                :key="mode"
                variant="ghost" 
                size="sm"
                :class="[editorMode === mode ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground']"
                @click="editorMode = mode"
                class="capitalize h-8"
            >
                <component :is="mode === 'classic' ? Type : Layout" class="w-3.5 h-3.5 mr-2" />
                {{ mode }}
            </Button>
        </div>

        <!-- Classic Editor -->
        <div v-if="editorMode === 'classic'" class="animate-in fade-in slide-in-from-top-2 duration-300">
            <TiptapEditor
                :model-value="modelValue.body"
                @update:model-value="updateField('body', $event)"
                class="min-h-[500px]"
            />
        </div>

        <!-- Page Builder -->
        <div v-else class="animate-in fade-in slide-in-from-top-2 duration-300">
            <Builder
                :model-value="modelValue.blocks || []"
                @update:model-value="updateField('blocks', $event)"
            />
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import TiptapEditor from '../TiptapEditor.vue';
import Builder from '../builder/Builder.vue';
import Button from '@/components/ui/button.vue';
import { Type, Layout } from 'lucide-vue-next';

const { t } = useI18n();

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue']);

// Determine initial mode: if blocks exist and not empty, use builder
const editorMode = ref(props.modelValue.blocks && props.modelValue.blocks.length > 0 ? 'builder' : 'classic');

const updateField = (field, value) => {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
};

const updateTitle = (newTitle) => {
    emit('update:modelValue', { ...props.modelValue, title: newTitle });
};
</script>
