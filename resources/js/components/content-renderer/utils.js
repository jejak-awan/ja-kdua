export const generateUUID = () => {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
        return crypto.randomUUID();
    }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
};

export function getBorderStyles(borderSettings) {
    if (!borderSettings) return {}
    const css = {}

    // Radius
    if (borderSettings.radius) {
        const { tl, tr, bl, br } = borderSettings.radius
        if (tl !== undefined) css.borderTopLeftRadius = `${tl}px`
        if (tr !== undefined) css.borderTopRightRadius = `${tr}px`
        if (bl !== undefined) css.borderBottomLeftRadius = `${bl}px`
        if (br !== undefined) css.borderBottomRightRadius = `${br}px`
    }

    // Border Sides
    if (borderSettings.styles) {
        const sides = ['top', 'right', 'bottom', 'left']
        sides.forEach(side => {
            const conf = borderSettings.styles[side]
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
    const { top, bottom, left, right, unit } = spacingSettings
    const u = unit || 'px'

    if (top !== undefined) css[`${type}Top`] = `${top}${u}`
    if (bottom !== undefined) css[`${type}Bottom`] = `${bottom}${u}`
    if (left !== undefined) css[`${type}Left`] = `${left}${u}`
    if (right !== undefined) css[`${type}Right`] = `${right}${u}`

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
    if (settings.backgroundColor) {
        css.backgroundColor = settings.backgroundColor
    }

    // Background Image & Gradients (Multiple Layers)
    const layers = []
    const sizes = []

    // 1. Resolve Gradients
    const gradientList = settings.backgroundGradients || (settings.backgroundGradient ? [settings.backgroundGradient] : [])
    const gradientCSSList = gradientList.map(g => generateGradientCSS(g)).filter(c => !!c)

    // 2. Resolve Image
    const imageCSS = settings.backgroundImage ? `url(${settings.backgroundImage})` : ''

    // 3. Assemble Layers
    let resolvedImageSize = ''
    if (imageCSS) {
        let size = settings.backgroundImageSize || 'cover'
        if (size === 'custom') {
            const w = settings.backgroundImageWidth || 'auto'
            const h = settings.backgroundImageHeight || 'auto'
            size = `${w} ${h}`
        } else if (size === 'stretch') {
            size = '100% 100%'
        } else if (size === 'fit') {
            size = 'contain'
        }
        resolvedImageSize = size
    }

    // 3.2. Add Gradient & Image based on "Above Image" setting
    if (settings.backgroundGradientShowAboveImage) {
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

    if (settings.backgroundGradientShowAboveImage) {
        gradientCSSList.forEach(() => {
            repeatList.push('no-repeat')
            blendList.push('normal')
        })
        if (imageCSS) {
            repeatList.push(settings.backgroundImageRepeat || 'no-repeat')
            blendList.push(settings.backgroundImageBlendMode || 'normal')
        }
    } else {
        if (imageCSS) {
            repeatList.push(settings.backgroundImageRepeat || 'no-repeat')
            blendList.push(settings.backgroundImageBlendMode || 'normal')
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
        if (settings.parallax && (settings.parallaxMethod === 'css' || !settings.parallaxMethod)) {
            css.backgroundAttachment = layers.map(() => 'fixed').join(', ')
        } else if (settings.backgroundImageAttachment) {
            css.backgroundAttachment = layers.map(() => settings.backgroundImageAttachment).join(', ')
        }

        // Position
        const posList = []
        if (settings.backgroundGradientShowAboveImage) {
            gradientCSSList.forEach(() => posList.push('center'))
            if (imageCSS) posList.push(settings.backgroundImagePosition || 'center')
        } else {
            if (imageCSS) posList.push(settings.backgroundImagePosition || 'center')
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
    if (settings.width && settings.width !== 'auto') {
        css.width = settings.width
    }
    if (settings.maxWidth && settings.maxWidth !== 'none') {
        css.maxWidth = settings.maxWidth
    }
    if (settings.minWidth) {
        css.minWidth = settings.minWidth
    }

    // Height
    if (settings.height && settings.height !== 'auto') {
        css.height = settings.height
    }
    if (settings.minHeight) {
        css.minHeight = settings.minHeight
    }
    if (settings.maxHeight && settings.maxHeight !== 'none') {
        css.maxHeight = settings.maxHeight
    }

    // Overflow
    if (settings.overflow && settings.overflow !== 'visible') {
        css.overflow = settings.overflow
        if (settings.overflowX) css.overflowX = settings.overflowX
        if (settings.overflowY) css.overflowY = settings.overflowY
    }

    // Z-Index
    if (settings.zIndex !== undefined && settings.zIndex !== '') {
        css.zIndex = settings.zIndex
    }

    return css
}
