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

        <!-- Editor Mode Toggle (Only show after selection) -->
        <div v-if="editorMode" class="flex items-center gap-1 p-1 bg-muted rounded-lg w-fit">
            <Button 
                v-for="mode in ['classic', 'builder']" 
                :key="mode"
                variant="ghost" 
                size="sm"
                :class="[editorMode === mode ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground']"
                @click="handleModeSwitch(mode)"
                class="capitalize h-8"
            >
                <component :is="mode === 'classic' ? Type : Layout" class="w-3.5 h-3.5 mr-2" />
                {{ mode }}
            </Button>
        </div>

        <!-- Editor Selection Screen -->
        <div v-if="!editorMode" class="py-20 border-2 border-dashed border-muted rounded-xl bg-muted/5 flex flex-col items-center justify-center text-center animate-in zoom-in-95 duration-500">
            <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center mb-6 ring-8 ring-primary/5">
                <Layout class="w-10 h-10 text-primary" />
            </div>
            <h2 class="text-2xl font-bold tracking-tight mb-2">{{ t('features.content.form.welcomeToBuilder') || 'Build your layout' }}</h2>
            <p class="text-muted-foreground mb-8 max-w-sm mx-auto">
                {{ t('features.content.form.chooseEditorDesc') || 'Choose between the standard text editor or our visual drag-and-drop builder.' }}
            </p>
            <div class="flex items-center gap-4">
                <Button 
                    size="lg" 
                    class="bg-primary text-primary-foreground shadow-lg hover:shadow-xl transition-all"
                    @click="confirmInitialMode('builder')"
                >
                    <Layout class="w-4 h-4 mr-2" />
                    {{ t('features.content.form.useBuilder') || 'Use JA-Builder' }}
                </Button>
                <Button 
                    variant="outline" 
                    size="lg"
                    @click="confirmInitialMode('classic')"
                    class="bg-background"
                >
                    <Type class="w-4 h-4 mr-2" />
                    {{ t('features.content.form.useDefault') || 'Use Classic Editor' }}
                </Button>
            </div>
        </div>

        <!-- Classic Editor -->
        <div v-else-if="editorMode === 'classic'" class="animate-in fade-in slide-in-from-top-2 duration-300">
            <TiptapEditor
                :model-value="modelValue.body"
                @update:model-value="updateField('body', $event)"
                class="min-h-[500px]"
            />
        </div>

        <!-- Page Builder -->
        <div v-else-if="editorMode === 'builder'" class="animate-in fade-in slide-in-from-top-2 duration-300">
            <Builder
                :model-value="modelValue.blocks || []"
                :mode="'page'"
                @update:model-value="updateField('blocks', $event)"
                @save="(status) => $emit('save', status)"
                @update:auto-save="(val) => $emit('toggle-auto-save', val)"
                @close="handleBuilderClose"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'; // Added nextTick
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router'; // Added useRouter
import TiptapEditor from '../TiptapEditor.vue';
import Builder from '../builder/Builder.vue';
import { htmlToBuilder, builderToHtml } from '@/utils/builderTransformer';
import Button from '@/components/ui/button.vue';
import { Type, Layout } from 'lucide-vue-next';

const { t } = useI18n();
const router = useRouter();

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'save', 'mode-selected', 'toggle-auto-save', 'cancel']);

// ... (methods)

// Determine initial mode: if editor_type exists, use it. Otherwise start with null (Selection Screen)
const editorMode = ref(props.modelValue.editor_type || null);

// Emit initial mode to parent if it's already set (e.g. for edit)
if (editorMode.value) {
    setTimeout(() => {
        emit('mode-selected', editorMode.value);
    }, 0);
}

// Check if content exists in the other mode
const hasClassicContent = computed(() => {
    const body = props.modelValue.body;
    return body && body.trim().length > 0 && body !== '<p></p>';
});

const hasBuilderContent = computed(() => {
    const blocks = props.modelValue.blocks;
    return blocks && blocks.length > 0;
});

const handleModeSwitch = async (newMode) => {
    if (newMode === editorMode.value) return;

    // Transform content based on new mode
    if (newMode === 'builder' && hasClassicContent.value) {
        // Transform HTML to Builder
        const newBlocks = htmlToBuilder(props.modelValue.body);
        updateField('blocks', newBlocks);
        
        // Wait for parent to update props before switching UI
        await nextTick();
        await nextTick(); // Double tick for safety with Inertia forms
    } else if (newMode === 'classic' && hasBuilderContent.value) {
        // Transform Builder to HTML
        const newHtml = builderToHtml(props.modelValue.blocks);
        updateField('body', newHtml);
        
        await nextTick();
        await nextTick();
    }

    editorMode.value = newMode;
    emit('mode-selected', newMode);
    updateField('editor_type', newMode);
};

const confirmInitialMode = (mode) => {
    editorMode.value = mode;
    emit('mode-selected', mode);
    updateField('editor_type', mode);
};

const handleBuilderClose = () => {
    emit('cancel');
};

const updateField = (field, value) => {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
};

const updateTitle = (newTitle) => {
    emit('update:modelValue', { ...props.modelValue, title: newTitle });
};
</script>
