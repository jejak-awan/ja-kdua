import * as SharedStyles from '../../shared/utils/styleUtils'

/**
 * Legacy Content Renderer Utilities - Now proxying to Shared Logic
 */

export const getVal = SharedStyles.getVal
export const toCSS = SharedStyles.toCSS
export const getBorderStyles = SharedStyles.getBorderStyles
export const getSpacingStyles = SharedStyles.getSpacingStyles
export const getBoxShadowStyles = SharedStyles.getBoxShadowStyles
export const getBackgroundStyles = SharedStyles.getBackgroundStyles
export const getSizingStyles = SharedStyles.getSizingStyles
export const getTransformStyles = SharedStyles.getTransformStyles
export const getLayoutStyles = SharedStyles.getLayoutStyles

// Helper that was only in renderer
export const generateUUID = () => {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
        return crypto.randomUUID();
    }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
};

// Legacy resolve function
export const resolve = (val, device = 'desktop') => SharedStyles.getResponsiveValue({ key: val }, 'key', device)
