<template>
    <Dialog :open="isOpen" @update:open="handleClose">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <component 
                        :is="variantIcons[variant]" 
                        :class="variantColors[variant]"
                        class="w-5 h-5" 
                    />
                    {{ title }}
                </DialogTitle>
                <DialogDescription v-if="description">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="message" class="py-4">
                <p class="text-sm text-muted-foreground whitespace-pre-wrap break-words">{{ message }}</p>
            </div>

            <div v-if="input" class="pb-4">
                <Input
                    v-model="inputValue"
                    :placeholder="inputPlaceholder"
                    class="w-full"
                    @keyup.enter="handleConfirm"
                    autofocus
                />
            </div>

            <DialogFooter class="gap-2 sm:gap-0">
                <Button
                    variant="outline"
                    @click="handleCancel"
                >
                    {{ cancelText }}
                </Button>
                <Button
                    :variant="confirmVariant"
                    @click="handleConfirm"
                >
                    {{ confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import Info from 'lucide-vue-next/dist/esm/icons/info.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import HelpCircle from 'lucide-vue-next/dist/esm/icons/circle-question-mark.js';
import CheckCircle2 from 'lucide-vue-next/dist/esm/icons/circle-check-big.js';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    Input,
    Button
} from '@/components/ui';

type ConfirmVariant = 'warning' | 'danger' | 'destructive' | 'info' | 'question' | 'success';

const props = withDefaults(defineProps<{
    isOpen?: boolean;
    title: string;
    description?: string;
    message?: string;
    variant?: ConfirmVariant;
    confirmText?: string;
    cancelText?: string;
    input?: boolean;
    inputPlaceholder?: string;
}>(), {
    isOpen: false,
    description: '',
    message: '',
    variant: 'warning',
    confirmText: 'OK',
    cancelText: 'Cancel',
    input: false,
    inputPlaceholder: '',
});

const emit = defineEmits<{
    'confirm': [value: string | boolean];
    'cancel': [];
    'update:isOpen': [value: boolean];
}>();

const inputValue = ref('');

// Reset input when modal opens
watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        inputValue.value = '';
    }
});

const variantIcons: Record<ConfirmVariant, any> = {
    warning: AlertTriangle,
    danger: Trash2,
    destructive: Trash2,
    info: Info,
    question: HelpCircle,
    success: CheckCircle2
};

const variantColors: Record<ConfirmVariant, string> = {
    warning: 'text-amber-500',
    danger: 'text-destructive',
    destructive: 'text-destructive',
    info: 'text-blue-500',
    question: 'text-primary',
    success: 'text-emerald-500'
};

const confirmVariant = computed(() => {
    if (props.variant === 'danger' || props.variant === 'destructive') return 'destructive';
    return 'default';
});

const handleConfirm = () => {
    emit('confirm', props.input ? inputValue.value : true);
    emit('update:isOpen', false);
};

const handleCancel = () => {
    emit('cancel');
    emit('update:isOpen', false);
};

const handleClose = (value: boolean) => {
    if (!value) {
        emit('cancel');
    }
    emit('update:isOpen', value);
};
</script>
