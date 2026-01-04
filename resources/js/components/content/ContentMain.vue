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
                @click="handleModeSwitch(mode)"
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

        <!-- Mode Switch Confirmation Dialog -->
        <Dialog :open="showSwitchDialog" @update:open="showSwitchDialog = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ t('features.content.form.switchModeTitle') }}</DialogTitle>
                    <DialogDescription>
                        {{ t('features.content.form.switchModeDescription') }}
                    </DialogDescription>
                </DialogHeader>
                <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-lg text-amber-600 dark:text-amber-400 text-sm">
                    <strong>{{ t('common.labels.warning') }}:</strong> 
                    {{ pendingMode === 'builder' 
                        ? t('features.content.form.switchToBuilderWarning') 
                        : t('features.content.form.switchToClassicWarning') 
                    }}
                </div>
                <DialogFooter class="flex gap-2 sm:gap-0">
                    <Button variant="outline" @click="cancelModeSwitch">
                        {{ t('common.actions.cancel') }}
                    </Button>
                    <Button @click="confirmModeSwitch">
                        {{ t('common.actions.continue') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import TiptapEditor from '../TiptapEditor.vue';
import Builder from '../builder/Builder.vue';
import Button from '@/components/ui/button.vue';
import Dialog from '@/components/ui/dialog.vue';
import DialogContent from '@/components/ui/dialog-content.vue';
import DialogHeader from '@/components/ui/dialog-header.vue';
import DialogTitle from '@/components/ui/dialog-title.vue';
import DialogDescription from '@/components/ui/dialog-description.vue';
import DialogFooter from '@/components/ui/dialog-footer.vue';
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

// Dialog state
const showSwitchDialog = ref(false);
const pendingMode = ref(null);

// Check if content exists in the other mode
const hasClassicContent = computed(() => {
    const body = props.modelValue.body;
    return body && body.trim().length > 0 && body !== '<p></p>';
});

const hasBuilderContent = computed(() => {
    const blocks = props.modelValue.blocks;
    return blocks && blocks.length > 0;
});

const handleModeSwitch = (newMode) => {
    if (newMode === editorMode.value) return;

    // Check if switching would lose content
    const switchingToBuilder = newMode === 'builder';
    const hasContentInCurrentMode = switchingToBuilder ? hasClassicContent.value : hasBuilderContent.value;

    if (hasContentInCurrentMode) {
        // Show warning dialog
        pendingMode.value = newMode;
        showSwitchDialog.value = true;
    } else {
        // No content to lose, switch immediately
        editorMode.value = newMode;
    }
};

const confirmModeSwitch = () => {
    if (pendingMode.value) {
        editorMode.value = pendingMode.value;
    }
    showSwitchDialog.value = false;
    pendingMode.value = null;
};

const cancelModeSwitch = () => {
    showSwitchDialog.value = false;
    pendingMode.value = null;
};

const updateField = (field, value) => {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
};

const updateTitle = (newTitle) => {
    emit('update:modelValue', { ...props.modelValue, title: newTitle });
};
</script>
