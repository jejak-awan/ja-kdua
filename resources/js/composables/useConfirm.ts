import { ref } from 'vue';

export interface ConfirmOptions {
    title?: string;
    message?: string; // Legacy support
    description?: string;
    variant?: 'warning' | 'danger' | 'info' | 'success';
    confirmText?: string;
    cancelText?: string;
    input?: boolean;
    inputPlaceholder?: string;
}

interface ConfirmState extends ConfirmOptions {
    isOpen: boolean;
    onConfirm: (() => void) | null;
    onCancel: (() => void) | null;
}

const confirmState = ref<ConfirmState>({
    isOpen: false,
    title: '',
    message: '',
    description: '',
    variant: 'warning',
    confirmText: 'OK',
    cancelText: 'Cancel',
    onConfirm: null,
    onCancel: null
});

export function useConfirm() {
    const confirm = (options: ConfirmOptions): Promise<any> => {
        return new Promise((resolve) => {
            const inputValue = ref('');
            confirmState.value = {
                isOpen: true,
                title: options.title || 'Confirm',
                message: options.message || '',
                description: options.description || '',
                variant: options.variant || 'warning',
                confirmText: options.confirmText || 'OK',
                cancelText: options.cancelText || 'Cancel',
                input: options.input || false,
                inputPlaceholder: options.inputPlaceholder || '',
                onConfirm: (val?: string) => {
                    resolve(options.input ? val : true);
                    confirmState.value.isOpen = false;
                },
                onCancel: () => {
                    resolve(false);
                    confirmState.value.isOpen = false;
                }
            };
        });
    };

    return {
        confirmState,
        confirm
    };
}
