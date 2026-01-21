import * as SharedStyles from '../../../shared/utils/styleUtils'

/**
 * Legacy Style Utilities - Now proxying to Shared Logic
 * This file is kept for backward compatibility with blocks that haven't been migrated yet.
 */

export const getResponsiveValue = SharedStyles.getResponsiveValue
export const getTypographyStyles = SharedStyles.getTypographyStyles
export const getSizingStyles = SharedStyles.getSizingStyles
export const getBorderStyles = SharedStyles.getBorderStyles
export const getSpacingStyles = SharedStyles.getSpacingStyles
export const getBoxShadowStyles = SharedStyles.getBoxShadowStyles
export const getBackgroundStyles = SharedStyles.getBackgroundStyles
export const getFilterStyles = SharedStyles.getFilterStyles
export const getTransformStyles = SharedStyles.getTransformStyles
export const getAnimationStyles = SharedStyles.getAnimationStyles
export const getHarmoniousGradientColors = SharedStyles.getHarmoniousGradientColors

// Keep some internal constants or special functions if they aren't in shared yet
export { BackgroundPatterns, BackgroundMasks } from './AssetLibrary'

// Gradient generation might be slightly different in legacy, let's proxy if compatible
export const generateGradientCSS = (gradient) => {
    if (!gradient || !gradient.stops) return ''
    const stops = gradient.stops.map(s => `${s.color} ${s.position}%`).join(', ')
    return `linear-gradient(${gradient.direction || '180deg'}, ${stops})`
}
