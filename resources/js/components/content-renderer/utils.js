export const generateUUID = () => {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
        return crypto.randomUUID();
    }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
};

/**
 * Robust value getter that checks for both camelCase and snake_case keys.
 */
function getVal(obj, key) {
    if (!obj) return undefined;
    if (obj[key] !== undefined && obj[key] !== null && obj[key] !== '') return obj[key];

    // Check snake_case variant
    const snakeKey = key.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
    if (obj[snakeKey] !== undefined && obj[snakeKey] !== null && obj[snakeKey] !== '') return obj[snakeKey];

    return undefined;
}

/**
 * Appends px unit if value is numeric.
 */
function addPx(val) {
    if (val === undefined || val === null || val === '') return val;
    if (typeof val === 'number') return `${val}px`;
    if (typeof val === 'string' && val.match(/^\-?\d+$/)) return `${val}px`;
    return val;
}

export function getBorderStyles(borderSettings) {
    if (!borderSettings) return {}
    const css = {}

    // Radius
    const radius = getVal(borderSettings, 'radius')
    if (radius) {
        const { tl, tr, bl, br } = radius
        if (tl !== undefined) css.borderTopLeftRadius = `${tl}px`
        if (tr !== undefined) css.borderTopRightRadius = `${tr}px`
        if (bl !== undefined) css.borderBottomLeftRadius = `${bl}px`
        if (br !== undefined) css.borderBottomRightRadius = `${br}px`
    }

    // Border Sides
    const borderStyles = getVal(borderSettings, 'styles')
    if (borderStyles) {
        const sides = ['top', 'right', 'bottom', 'left']
        sides.forEach(side => {
            const conf = borderStyles[side]
            if (conf) {
                const sideCap = side.charAt(0).toUpperCase() + side.slice(1)

                // If width is > 0 or style is set (and not none)
                if (conf.width > 0 && conf.style !== 'none') {
                    css[`border${sideCap}Width`] = `${conf.width}px`
                    css[`border${sideCap}Style`] = conf.style || 'solid'
                    css[`border${sideCap}Color`] = conf.color || 'transparent'
                }
            }
        })
    }

    return css
}

export function getSpacingStyles(spacingSettings, type = 'padding') {
    if (!spacingSettings) return {}
    const css = {}

    const top = getVal(spacingSettings, 'top')
    const bottom = getVal(spacingSettings, 'bottom')
    const left = getVal(spacingSettings, 'left')
    const right = getVal(spacingSettings, 'right')
    const unit = getVal(spacingSettings, 'unit') || 'px'

    if (top !== undefined) css[`${type}Top`] = `${top}${unit}`
    if (bottom !== undefined) css[`${type}Bottom`] = `${bottom}${unit}`
    if (left !== undefined) css[`${type}Left`] = `${left}${unit}`
    if (right !== undefined) css[`${type}Right`] = `${right}${unit}`

    return css
}

export function getBoxShadowStyles(shadowSettings) {
    if (!shadowSettings || shadowSettings.preset === 'none') return {}

    const { horizontal, vertical, blur, spread, color, inset } = shadowSettings
    const h = horizontal || 0
    const v = vertical || 0
    const b = blur || 0
    const s = spread || 0
    const c = color || 'rgba(0,0,0,0)'
    const i = inset ? 'inset ' : ''

    return {
        boxShadow: `${i}${h}px ${v}px ${b}px ${s}px ${c}`
    }
}

export function generateGradientCSS(gradient) {
    if (!gradient || !gradient.stops || gradient.stops.length < 2) return ''

    const { type = 'linear', direction = '180deg', stops, repeat = false } = gradient
    const prefix = repeat ? 'repeating-' : ''

    // Sort stops by position
    const sortedStops = [...stops].sort((a, b) => a.position - b.position)
    const stopString = sortedStops.map(s => `${s.color} ${s.position}%`).join(', ')

    if (type === 'linear') {
        return `${prefix}linear-gradient(${direction}, ${stopString})`
    } else if (type === 'circular') {
        return `${prefix}radial-gradient(circle, ${stopString})`
    } else if (type === 'elliptical') {
        return `${prefix}radial-gradient(ellipse, ${stopString})`
    } else if (type === 'conical') {
        return `${prefix}conic-gradient(from ${direction}, ${stopString})`
    }

    return ''
}

export function getBackgroundStyles(settings) {
    if (!settings) return {}
    const css = {}

    // Background Color
    const bgColor = getVal(settings, 'backgroundColor')
    if (bgColor) {
        css.backgroundColor = bgColor
    }

    // Background Image & Gradients (Multiple Layers)
    const layers = []
    const sizes = []

    // 1. Resolve Gradients
    const gradientList = getVal(settings, 'backgroundGradients') || (getVal(settings, 'backgroundGradient') ? [getVal(settings, 'backgroundGradient')] : [])
    const gradientCSSList = gradientList.map(g => generateGradientCSS(g)).filter(c => !!c)

    // 2. Resolve Image
    const bgImage = getVal(settings, 'backgroundImage')
    const imageCSS = bgImage ? `url(${bgImage})` : ''

    // 3. Assemble Layers
    let resolvedImageSize = ''
    if (imageCSS) {
        let size = getVal(settings, 'backgroundImageSize') || 'cover'
        if (size === 'custom') {
            const w = getVal(settings, 'backgroundImageWidth') || 'auto'
            const h = getVal(settings, 'backgroundImageHeight') || 'auto'
            size = `${w} ${h}`
        } else if (size === 'stretch') {
            size = '100% 100%'
        } else if (size === 'fit') {
            size = 'contain'
        }
        resolvedImageSize = size
    }

    // 3.2. Add Gradient & Image based on "Above Image" setting
    if (getVal(settings, 'backgroundGradientShowAboveImage')) {
        gradientCSSList.forEach((gcss, index) => {
            layers.push(gcss)
            const g = gradientList[index]
            sizes.push(g?.length ? `${g.length} auto` : '100% 100%')
        })
        if (imageCSS) {
            layers.push(imageCSS)
            sizes.push(resolvedImageSize)
        }
    } else {
        if (imageCSS) {
            layers.push(imageCSS)
            sizes.push(resolvedImageSize)
        }
        gradientCSSList.forEach((gcss, index) => {
            layers.push(gcss)
            const g = gradientList[index]
            sizes.push(g?.length ? `${g.length} auto` : '100% 100%')
        })
    }

    if (layers.length > 0) {
        css.backgroundImage = layers.join(', ')
        css.backgroundSize = sizes.join(', ')
    }

    const repeatList = []
    const blendList = []

    if (getVal(settings, 'backgroundGradientShowAboveImage')) {
        gradientCSSList.forEach(() => {
            repeatList.push('no-repeat')
            blendList.push('normal')
        })
        if (imageCSS) {
            repeatList.push(getVal(settings, 'backgroundImageRepeat') || 'no-repeat')
            blendList.push(getVal(settings, 'backgroundImageBlendMode') || 'normal')
        }
    } else {
        if (imageCSS) {
            repeatList.push(getVal(settings, 'backgroundImageRepeat') || 'no-repeat')
            blendList.push(getVal(settings, 'backgroundImageBlendMode') || 'normal')
        }
        gradientCSSList.forEach(() => {
            repeatList.push('no-repeat')
            blendList.push('normal')
        })
    }

    if (layers.length > 0) {
        css.backgroundImage = layers.join(', ')
        css.backgroundSize = sizes.join(', ')
        css.backgroundRepeat = repeatList.join(', ')
        css.backgroundBlendMode = blendList.join(', ')

        // Attachment / Parallax
        const parallax = getVal(settings, 'parallax')
        const parallaxMethod = getVal(settings, 'parallaxMethod')
        if (parallax && (parallaxMethod === 'css' || !parallaxMethod)) {
            css.backgroundAttachment = layers.map(() => 'fixed').join(', ')
        } else if (getVal(settings, 'backgroundImageAttachment')) {
            css.backgroundAttachment = layers.map(() => getVal(settings, 'backgroundImageAttachment')).join(', ')
        }

        // Position
        const posList = []
        if (getVal(settings, 'backgroundGradientShowAboveImage')) {
            gradientCSSList.forEach(() => posList.push('center'))
            if (imageCSS) posList.push(getVal(settings, 'backgroundImagePosition') || 'center')
        } else {
            if (imageCSS) posList.push(getVal(settings, 'backgroundImagePosition') || 'center')
            gradientCSSList.forEach(() => posList.push('center'))
        }
        css.backgroundPosition = posList.join(', ')
    }

    return css
}

export function getSizingStyles(settings) {
    if (!settings) return {}
    const css = {}

    // Width
    const width = getVal(settings, 'width')
    if (width && width !== 'auto') css.width = addPx(width)

    const maxWidth = getVal(settings, 'maxWidth')
    if (maxWidth && maxWidth !== 'none') css.maxWidth = addPx(maxWidth)

    const minWidth = getVal(settings, 'minWidth')
    if (minWidth) css.minWidth = addPx(minWidth)

    // Height
    const height = getVal(settings, 'height')
    if (height && height !== 'auto') css.height = addPx(height)

    const minHeight = getVal(settings, 'minHeight')
    if (minHeight) css.minHeight = addPx(minHeight)

    const maxHeight = getVal(settings, 'maxHeight')
    if (maxHeight && maxHeight !== 'none') css.maxHeight = addPx(maxHeight)

    // Overflow
    const overflow = getVal(settings, 'overflow')
    if (overflow && overflow !== 'visible') {
        css.overflow = overflow
        if (getVal(settings, 'overflowX')) css.overflowX = getVal(settings, 'overflowX')
        if (getVal(settings, 'overflowY')) css.overflowY = getVal(settings, 'overflowY')
    }

    // Z-Index
    const zIndex = getVal(settings, 'zIndex')
    if (zIndex !== undefined && zIndex !== null && zIndex !== '') {
        css.zIndex = zIndex
    }

    return css
}
