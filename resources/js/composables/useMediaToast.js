import { useI18n } from 'vue-i18n';
import toast from '../services/toast';

export function useMediaToast() {
    const { t } = useI18n();

    return {
        success: {
            upload: () => toast.success(t('features.media.toast.uploadSuccess')),
            update: () => toast.success(t('features.media.toast.updateSuccess')),
            delete: () => toast.success(t('features.media.toast.deleteSuccess')),
            restore: () => toast.success(t('features.media.toast.restoreSuccess')),
            move: () => toast.success(t('features.media.toast.moveSuccess')),
            folderCreated: () => toast.success(t('features.media.toast.folderCreatedSuccess')),
            urlCopied: () => toast.success(t('features.media.toast.urlCopied')),
            thumbnail: () => toast.success(t('features.media.toast.thumbnailSuccess')),
        },
        error: {
            generic: (message) => toast.error('Error', message),
            permission: () => toast.error('Permission Denied', t('features.media.toast.permissionDenied')),
            fileTooLarge: () => toast.warning('File Too Large', t('features.media.toast.fileTooLarge')),
            fromResponse: (error) => {
                const message = error?.response?.data?.message || error?.message || 'An error occurred';
                toast.error('Error', message);
            }
        }
    };
}
