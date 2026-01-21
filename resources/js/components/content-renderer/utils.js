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

    // Check shorthand aliases
    const aliases = {
        'backgroundColor': ['bgColor'],
        'backgroundImage': ['bgImage'],
        'backgroundSize': ['bgSize'],
        'backgroundPosition': ['bgPos', 'bgPosition'],
        'backgroundRepeat': ['bgRepeat'],
        'borderRadius': ['radius', 'r'],
        'minHeight': ['min_height'],
        'maxHeight': ['max_height'],
        'minWidth': ['min_width'],
        'maxWidth': ['max_width']
    };

    if (aliases[key]) {
        for (const alias of aliases[key]) {
            if (obj[alias] !== undefined && obj[alias] !== null && obj[alias] !== '') return obj[alias];
        }
    }

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
        if (typeof radius === 'object') {
            const { tl, tr, bl, br } = radius
            if (tl !== undefined) css.borderTopLeftRadius = `${tl}px`
            if (tr !== undefined) css.borderTopRightRadius = `${tr}px`
            if (bl !== undefined) css.borderBottomLeftRadius = `${bl}px`
            if (br !== undefined) css.borderBottomRightRadius = `${br}px`
        }
    }

    // Border Sides
    const borderStyles = getVal(borderSettings, 'styles')
    if (borderStyles) {
        const sides = ['top', 'right', 'bottom', 'left']
        sides.forEach(side => {
            const conf = borderStyles[side]
            if (conf) {
                const sideCap = side.charAt(0).toUpperCase() + side.slice(1)
                if (conf.width > 0 && conf.style !== 'none') {
                    css[`border${sideCap}Width`] = `${conf.width}px`
                    css[`border${sideCap}Style`] = conf.style || 'solid'
                    css[`border${sideCap}Color`] = conf.color || 'transparent'
                }
            }
        })
    } else {
        const w = getVal(borderSettings, 'borderWidth')
        if (w !== undefined) {
            const c = getVal(borderSettings, 'borderColor') || '#000'
            css.borderWidth = `${w}px`
            css.borderStyle = 'solid'
            css.borderColor = c
        }
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

    const glue = (v) => {
        if (v === undefined || v === null || v === '') return undefined
        if (typeof v === 'string' && (v.includes('px') || v.includes('rem') || v.includes('%') || v.includes('vh') || v.includes('vw'))) return v
        return `${v}${unit}`
    }

    if (top !== undefined) css[`${type}Top`] = glue(top)
    if (bottom !== undefined) css[`${type}Bottom`] = glue(bottom)
    if (left !== undefined) css[`${type}Left`] = glue(left)
    if (right !== undefined) css[`${type}Right`] = glue(right)

    return css
}

export function getBoxShadowStyles(shadowSettings) {
    if (!shadowSettings || shadowSettings === 'none' || shadowSettings.preset === 'none') return {}
    const { horizontal, vertical, blur, spread, color, inset } = shadowSettings
    const h = horizontal || 0
    const v = vertical || 0
    const b = blur || 0
    const s = spread || 0
    const c = color || 'rgba(0,0,0,0)'
    const i = inset ? 'inset ' : ''
    return { boxShadow: `${i}${h}px ${v}px ${b}px ${s}px ${c}` }
}

export function generateGradientCSS(gradient) {
    if (!gradient || !gradient.stops || gradient.stops.length < 2) return ''
    const { type = 'linear', direction = '180deg', stops, repeat = false } = gradient
    const prefix = repeat ? 'repeating-' : ''
    const sortedStops = [...stops].sort((a, b) => a.position - b.position)
    const stopString = sortedStops.map(s => `${s.color} ${s.position}%`).join(', ')
    if (type === 'linear') return `${prefix}linear-gradient(${direction}, ${stopString})`
    else if (type === 'circular') return `${prefix}radial-gradient(circle, ${stopString})`
    else if (type === 'elliptical') return `${prefix}radial-gradient(ellipse, ${stopString})`
    else if (type === 'conical') return `${prefix}conic-gradient(from ${direction}, ${stopString})`
    return ''
}

export function getBackgroundStyles(settings) {
    if (!settings) return {}
    const css = {}
    const bgColor = getVal(settings, 'backgroundColor')
    if (bgColor) css.backgroundColor = bgColor

    const layers = []
    const sizes = []

    const gradientList = getVal(settings, 'backgroundGradients') || (getVal(settings, 'backgroundGradient') ? [getVal(settings, 'backgroundGradient')] : [])
    if (gradientList.length === 0 && getVal(settings, 'gradientStart')) {
        const start = getVal(settings, 'gradientStart')
        const end = getVal(settings, 'gradientEnd') || start
        const dir = getVal(settings, 'gradientDirection') || 'to right'
        layers.push(`linear-gradient(${dir}, ${start}, ${end})`)
        sizes.push('cover')
    }
    const gradientCSSList = gradientList.map(g => generateGradientCSS(g)).filter(c => !!c)

    const bgImage = getVal(settings, 'backgroundImage')
    const imageCSS = bgImage ? `url(${bgImage})` : ''
    let resolvedImageSize = ''
    if (imageCSS) {
        let size = getVal(settings, 'backgroundImageSize') || 'cover'
        if (size === 'custom') {
            const w = getVal(settings, 'backgroundImageWidth') || 'auto'
            const h = getVal(settings, 'backgroundImageHeight') || 'auto'
            size = `${w} ${h}`
        } else if (size === 'stretch') size = '100% 100%'
        else if (size === 'fit') size = 'contain'
        resolvedImageSize = size
    }

    if (getVal(settings, 'backgroundGradientShowAboveImage')) {
        gradientCSSList.forEach((gcss) => { layers.push(gcss); sizes.push('100% 100%') })
        if (imageCSS) { layers.push(imageCSS); sizes.push(resolvedImageSize) }
    } else {
        if (imageCSS) { layers.push(imageCSS); sizes.push(resolvedImageSize) }
        gradientCSSList.forEach((gcss) => { layers.push(gcss); sizes.push('100% 100%') })
    }

    if (layers.length > 0) {
        css.backgroundImage = layers.join(', ')
        css.backgroundSize = sizes.join(', ')
        css.backgroundRepeat = layers.map(() => 'no-repeat').join(', ')
        css.backgroundPosition = layers.map(() => 'center').join(', ')
    }
    return css
}

export function getSizingStyles(settings) {
    if (!settings) return {}
    const css = {}
    const width = getVal(settings, 'width')
    if (width && width !== 'auto') css.width = addPx(width)
    const maxWidth = getVal(settings, 'maxWidth')
    if (maxWidth && maxWidth !== 'none') css.maxWidth = addPx(maxWidth)
    const minWidth = getVal(settings, 'minWidth')
    if (minWidth) css.minWidth = addPx(minWidth)
    const height = getVal(settings, 'height')
    if (height && height !== 'auto') css.height = addPx(height)
    const minHeight = getVal(settings, 'minHeight')
    if (minHeight) css.minHeight = addPx(minHeight)
    const maxHeight = getVal(settings, 'maxHeight')
    if (maxHeight && maxHeight !== 'none') css.maxHeight = addPx(maxHeight)
    const overflow = getVal(settings, 'overflow')
    if (overflow && overflow !== 'visible') {
        css.overflow = overflow
        if (getVal(settings, 'overflowX')) css.overflowX = getVal(settings, 'overflowX')
        if (getVal(settings, 'overflowY')) css.overflowY = getVal(settings, 'overflowY')
    }
    const zIndex = getVal(settings, 'zIndex')
    if (zIndex !== undefined && zIndex !== null && zIndex !== '') css.zIndex = zIndex
    return css
}

export function getTransformStyles(settings) {
    if (!settings) return {}
    const transforms = []
    const origin = getVal(settings, 'transformOrigin') || getVal(settings, 'origin')
    const scale = getVal(settings, 'transformScale') || getVal(settings, 'scale')
    if (scale !== undefined && scale !== null && scale !== '' && scale != 100) transforms.push(`scale(${scale / 100})`)
    const tx = getVal(settings, 'translateX') || getVal(settings, 'transformTranslateX') || 0
    const ty = getVal(settings, 'translateY') || getVal(settings, 'transformTranslateY') || 0
    if (tx != 0 || ty != 0) transforms.push(`translate(${addPx(tx)}, ${addPx(ty)})`)
    const rx = getVal(settings, 'rotateX') || getVal(settings, 'transformRotateX') || 0
    const ry = getVal(settings, 'rotateY') || getVal(settings, 'transformRotateY') || 0
    const rz = getVal(settings, 'rotateZ') || getVal(settings, 'rotate') || getVal(settings, 'transformRotate') || 0
    if (rx != 0) transforms.push(`rotateX(${rx}deg)`)
    if (ry != 0) transforms.push(`rotateY(${ry}deg)`)
    if (rz != 0) transforms.push(`rotateZ(${rz}deg)`)
    const sx = getVal(settings, 'skewX') || getVal(settings, 'transformSkewX') || 0
    const sy = getVal(settings, 'skewY') || getVal(settings, 'transformSkewY') || 0
    if (sx != 0) transforms.push(`skewX(${sx}deg)`)
    if (sy != 0) transforms.push(`skewY(${sy}deg)`)
    const css = {}
    if (transforms.length > 0) css.transform = transforms.join(' ')
    if (origin && origin !== 'center') css.transformOrigin = origin
    return css
}
