import { ref } from 'vue';

const confirmState = ref({
    isOpen: false,
    title: '',
    message: '',
    variant: 'warning',
    confirmText: 'OK',
    cancelText: 'Cancel',
    onConfirm: null,
    onCancel: null
});

export function useConfirm() {
    const confirm = (options) => {
        return new Promise((resolve) => {
            confirmState.value = {
                isOpen: true,
                title: options.title || 'Confirm',
                message: options.message || '',
                description: options.description || '',
                variant: options.variant || 'warning',
                confirmText: options.confirmText || 'OK',
                cancelText: options.cancelText || 'Cancel',
                onConfirm: () => {
                    resolve(true);
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
