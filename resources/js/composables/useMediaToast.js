import { useI18n } from 'vue-i18n';
import toast from '../services/toast';

export function useMediaToast() {
    const { t } = useI18n();

    // Map common backend English messages to translation keys
    const messageMap = {
        'You cannot update global media': 'features.media.errors.cannotUpdateGlobal',
        'You cannot delete global media': 'features.media.errors.cannotDeleteGlobal',
        'You do not have permission to update this media': 'features.media.errors.noPermissionUpdate',
        'You do not have permission to delete this media': 'features.media.errors.noPermissionDelete',
        'You do not have permission to manage this media': 'features.media.errors.noPermissionManage',
        'Media is currently in use': 'features.media.errors.mediaInUse',
        // Common API errors
        'Unauthorized': 'features.media.apiErrors.unauthorized',
        'Forbidden': 'features.media.apiErrors.forbidden',
        'Not Found': 'features.media.apiErrors.notFound',
        'Server Error': 'features.media.apiErrors.serverError',
    };

    const translateMessage = (message) => {
        // Check if message matches known backend messages
        if (messageMap[message]) {
            return t(messageMap[message]);
        }
        // Return original message if no translation found
        return message;
    };

    return {
        success: {
            upload: () => toast.success(t('features.media.toast.titles.success'), t('features.media.toast.uploadSuccess')),
            update: () => toast.success(t('features.media.toast.titles.success'), t('features.media.toast.updateSuccess')),
            delete: () => toast.success(t('features.media.toast.titles.success'), t('features.media.toast.deleteSuccess')),
            restore: () => toast.success(t('features.media.toast.titles.success'), t('features.media.toast.restoreSuccess')),
            move: () => toast.success(t('features.media.toast.titles.success'), t('features.media.toast.moveSuccess')),
            folderCreated: () => toast.success(t('features.media.toast.titles.success'), t('features.media.toast.folderCreatedSuccess')),
            urlCopied: () => toast.success(t('features.media.toast.titles.success'), t('features.media.toast.urlCopied')),
            thumbnail: () => toast.success(t('features.media.toast.titles.success'), t('features.media.toast.thumbnailSuccess')),
        },
        error: {
            generic: (message) => toast.error(t('features.media.toast.titles.error'), translateMessage(message)),
            permission: () => toast.error(t('features.media.toast.titles.permissionDenied'), t('features.media.toast.permissionDenied')),
            fileTooLarge: () => toast.warning(t('features.media.toast.titles.fileTooLarge'), t('features.media.toast.fileTooLarge')),
            fromResponse: (error) => {
                const message = error?.response?.data?.message || error?.message || t('features.media.toast.permissionDenied');
                toast.error(t('features.media.toast.titles.error'), translateMessage(message));
            }
        }
    };
}
