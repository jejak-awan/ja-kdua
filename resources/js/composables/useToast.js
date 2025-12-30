import { useI18n } from 'vue-i18n';
import toast from '../services/toast';

/**
 * Unified toast composable for application-wide notifications.
 * Merges logic from previous useContentToast and useMediaToast.
 */
export function useToast() {
    const { t } = useI18n();

    // Map common backend English messages to translation keys
    // Includes general, content-specific, and media-specific errors
    const messageMap = {
        // Generic & Content Errors
        'Validation failed. Please check your input.': 'common.messages.error.validation',
        'The given data was invalid.': 'common.messages.error.validation',
        'Unauthorized': 'common.messages.error.unauthorized',
        'Forbidden': 'common.messages.error.forbidden',
        'Not Found': 'common.messages.error.notFound',
        'Server Error': 'common.messages.error.server',

        // Media Specific Errors (all variations)
        'You do not have permission to delete media': 'features.media.errors.noPermissionDelete',
        'You do not have permission to update media': 'features.media.errors.noPermissionUpdate',
        'You do not have permission to restore media': 'features.media.errors.noPermissionUpdate',
        'You do not have permission to permanently delete media': 'features.media.errors.noPermissionDelete',
        'You do not have permission to edit media': 'features.media.errors.noPermissionUpdate',
        'You do not have permission to view this media': 'features.media.errors.noPermissionUpdate',
        'You do not have permission to update this media': 'features.media.errors.noPermissionUpdate',
        'You do not have permission to delete this media': 'features.media.errors.noPermissionDelete',
        'You do not have permission to restore this media': 'features.media.errors.noPermissionUpdate',
        'You do not have permission to manage this media': 'features.media.errors.noPermissionManage',
        'You cannot update global media': 'features.media.errors.cannotUpdateGlobal',
        'You cannot delete global media': 'features.media.errors.cannotDeleteGlobal',
        'You cannot restore global media': 'features.media.errors.cannotDeleteGlobal',
        'Media is currently in use': 'features.media.errors.mediaInUse',
    };

    /**
     * Translates backend messages using the messageMap and pattern matching
     */
    const translateMessage = (message) => {
        // First, try exact match in messageMap
        if (messageMap[message]) {
            return t(messageMap[message]);
        }

        // Pattern 1: "You do not have permission to {action} {resource}"
        const permissionPattern1 = /^You do not have permission to (.+)$/;
        const match1 = message.match(permissionPattern1);
        if (match1) {
            const action = match1[1];
            return t('common.messages.error.noPermission', { action });
        }

        // Pattern 2: "You cannot {action} global {resource}"
        const globalPattern = /^You cannot (.+) global (.+)$/;
        const match2 = message.match(globalPattern);
        if (match2) {
            const action = match2[1];
            const resource = match2[2];
            return t('common.messages.error.cannotActionGlobal', { action, resource });
        }

        // Pattern 3: "You can only {action}"
        const onlyPattern = /^You can only (.+)$/;
        const match3 = message.match(onlyPattern);
        if (match3) {
            const action = match3[1];
            return t('common.messages.error.canOnlyAction', { action });
        }

        // If no pattern matches, return original message
        return message;
    };

    /**
     * Extracts message from error object or returns default
     */
    const getErrorMessage = (error, defaultKey) => {
        // If it's a validation error (422), always use the validation translation
        if (error?.response?.status === 422) {
            return t('common.messages.error.validation');
        }

        const message = error?.response?.data?.message || error?.message || (defaultKey ? t(defaultKey) : '');
        return translateMessage(message);
    };

    return {
        // Access raw toast service
        service: toast,

        success: {
            create: (item = 'Data') => toast.success(t('common.messages.toast.success'), t('common.messages.success.created', { item })),
            update: (item = 'Data') => toast.success(t('common.messages.toast.success'), t('common.messages.success.updated', { item })),
            delete: (item = 'Data') => toast.success(t('common.messages.toast.success'), t('common.messages.success.deleted', { item })),
            save: () => toast.success(t('common.messages.toast.success'), t('common.messages.success.saved')),
            restore: (item = 'Data') => toast.success(t('common.messages.toast.success'), t('common.messages.success.updated', { item: `${item} restored` })),
            duplicate: (item = 'Data') => toast.success(t('common.messages.toast.success'), t('common.messages.success.created', { item: `${item} duplicate` })),
            action: (message) => toast.success(t('common.messages.toast.success'), message || t('common.messages.success.default')),

            // Generic status updates
            approve: (item = 'Item') => toast.success(t('common.messages.toast.success'), t('common.messages.success.approved', { item })),
            reject: (item = 'Item') => toast.success(t('common.messages.toast.success'), t('common.messages.success.rejected', { item })),
            markSpam: (item = 'Item') => toast.success(t('common.messages.toast.success'), t('common.messages.success.marked_spam', { item })),

            // Media Specific
            upload: () => toast.success(t('common.messages.toast.success'), t('features.media.toast.uploadSuccess') || 'File uploaded successfully'),
            move: () => toast.success(t('common.messages.toast.success'), t('features.media.toast.moveSuccess') || 'Item moved successfully'),
            folderCreated: () => toast.success(t('common.messages.toast.success'), t('features.media.toast.folderCreatedSuccess') || 'Folder created successfully'),
            urlCopied: () => toast.success(t('common.messages.toast.success'), t('features.media.toast.urlCopied') || 'URL copied to clipboard'),

            // Content Template Specific
            createFromTemplate: () => toast.success(t('common.messages.toast.success'), t('features.content_templates.messages.createContentSuccess') || 'Content created from template'),
        },

        error: {
            create: (error, item = 'Item') => toast.error(t('common.messages.toast.error'), getErrorMessage(error, 'common.messages.error.action')),
            update: (error, item = 'Item') => toast.error(t('common.messages.toast.error'), getErrorMessage(error, 'common.messages.error.action')),
            delete: (error, item = 'Item') => toast.error(t('common.messages.toast.error'), getErrorMessage(error, 'common.messages.error.deleteFailed', { item })),
            load: (error) => toast.error(t('common.messages.toast.error'), t('common.messages.error.loadFailed')),
            action: (error) => toast.error(t('common.messages.toast.error'), getErrorMessage(error, 'common.messages.error.action')),
            fromResponse: (error) => toast.error(t('common.messages.toast.error'), getErrorMessage(error, 'common.messages.error.action')),
            validation: (message) => toast.error(t('common.messages.toast.error'), message || t('common.messages.error.validation')),

            // Media Specific
            permission: () => toast.error(t('common.messages.toast.error'), t('features.media.toast.permissionDenied') || 'Permission denied'),
            fileTooLarge: () => toast.warning(t('common.messages.toast.warning'), t('features.media.toast.fileTooLarge') || 'File is too large'),

            // Specific Domain Errors
            templateCreateContent: (error) => toast.error(t('common.messages.toast.error'), getErrorMessage(error, 'features.content_templates.messages.createError')),
        },

        warning: (title, message) => toast.warning(title, message),
        info: (title, message) => toast.info(title, message),
    };
}
