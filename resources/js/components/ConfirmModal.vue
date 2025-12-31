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
                <p class="text-sm text-muted-foreground">{{ message }}</p>
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

<script setup>
import { ref, computed } from 'vue';
import { AlertTriangle, Info, Trash2, HelpCircle } from 'lucide-vue-next';
import Dialog from './ui/dialog.vue';
import DialogContent from './ui/dialog-content.vue';
import DialogDescription from './ui/dialog-description.vue';
import DialogFooter from './ui/dialog-footer.vue';
import DialogHeader from './ui/dialog-header.vue';
import DialogTitle from './ui/dialog-title.vue';
import Button from './ui/button.vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        default: ''
    },
    message: {
        type: String,
        default: ''
    },
    variant: {
        type: String,
        default: 'warning',
        validator: (value) => ['warning', 'danger', 'destructive', 'info', 'question', 'success'].includes(value)
    },
    confirmText: {
        type: String,
        default: 'OK'
    },
    cancelText: {
        type: String,
        default: 'Cancel'
    }
});

const emit = defineEmits(['confirm', 'cancel', 'update:isOpen']);

import { CheckCircle2 } from 'lucide-vue-next';

const variantIcons = {
    warning: AlertTriangle,
    danger: Trash2,
    destructive: Trash2,
    info: Info,
    question: HelpCircle,
    success: CheckCircle2
};

const variantColors = {
    warning: 'text-amber-500',
    danger: 'text-destructive',
    destructive: 'text-destructive',
    info: 'text-blue-500',
    question: 'text-primary',
    success: 'text-emerald-500'
};

const confirmVariant = computed(() => {
    if (props.variant === 'danger' || props.variant === 'destructive') return 'destructive';
    if (props.variant === 'success') return 'default'; // Or handled via class if dedicated success variant doesn't exist
    return 'default';
});

const handleConfirm = () => {
    emit('confirm');
    emit('update:isOpen', false);
};

const handleCancel = () => {
    emit('cancel');
    emit('update:isOpen', false);
};

const handleClose = (value) => {
    if (!value) {
        emit('cancel');
    }
    emit('update:isOpen', value);
};
</script>
