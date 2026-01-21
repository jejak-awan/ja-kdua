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
 * Resolves a potentially responsive value to a single value.
 * Inheritance: mobile -> tablet -> desktop
 */
export function resolve(val, device = 'desktop') {
    if (val === undefined || val === null) return val;
    if (typeof val !== 'object' || Array.isArray(val)) return val;

    // Check if it's a responsive object
    if ('desktop' in val || 'mobile' in val || 'tablet' in val) {
        const d = val.desktop;
        const t = (val.tablet !== undefined && val.tablet !== null && val.tablet !== '') ? val.tablet : d;
        const m = (val.mobile !== undefined && val.mobile !== null && val.mobile !== '') ? val.mobile : t;

        if (device === 'mobile') return m;
        if (device === 'tablet') return t;
        return d;
    }

    return val;
}

/**
 * Robust value getter that handles camelCase, snake_case, and Responsive Objects.
 */
export function getVal(obj, key, device = 'desktop') {
    if (!obj) return undefined;

    // Internal helper to get raw value including aliases and snake_case
    const getRaw = (innerObj, innerKey) => {
        if (!innerObj || typeof innerObj !== 'object') return undefined;
        if (innerKey === undefined) return innerObj;

        if (innerObj[innerKey] !== undefined && innerObj[innerKey] !== null && innerObj[innerKey] !== '') return innerObj[innerKey];

        const aliases = {
            'backgroundColor': ['bgColor', 'background_color'],
            'backgroundImage': ['bgImage', 'background_image'],
            'backgroundSize': ['bgSize', 'background_size'],
            'backgroundPosition': ['bgPos', 'bgPosition', 'background_position'],
            'backgroundRepeat': ['bgRepeat', 'background_repeat'],
            'borderRadius': ['radius', 'r', 'border_radius'],
            'minHeight': ['min_height'],
            'maxHeight': ['max_height'],
            'minWidth': ['min_width'],
            'maxWidth': ['max_width'],
            'verticalAlign': ['vertical_align', 'verticalAlignment']
        };

        if (aliases[innerKey]) {
            for (const alias of aliases[innerKey]) {
                if (innerObj[alias] !== undefined && innerObj[alias] !== null && innerObj[alias] !== '') return innerObj[alias];
            }
        }

        const snakeKey = innerKey.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
        if (innerObj[snakeKey] !== undefined && innerObj[snakeKey] !== null && innerObj[snakeKey] !== '') return innerObj[snakeKey];

        return undefined;
    }

    // 1. Resolve if the container object itself is a responsive object
    const source = resolve(obj, device);

    // 2. Try to get the key from the source
    const rawVal = getRaw(source, key);

    // 3. Resolve the result if it's responsive
    return resolve(rawVal, device);
}

/**
 * Safely appends unit to a value for CSS.
 */
export function toCSS(val, unit = 'px') {
    if (val === undefined || val === null || val === '' || typeof val === 'object') return undefined;
    if (typeof val === 'string' && (val.includes('px') || val.includes('rem') || val.includes('%') || val.includes('vh') || val.includes('vw') || val === 'auto' || val === 'inherit')) return val;
    return `${val}${unit}`;
}

export function getBorderStyles(borderSettings, device = 'desktop') {
    if (!borderSettings) return {}
    const css = {}

    // Radius
    const radius = getVal(borderSettings, 'radius', device)
    if (radius) {
        if (typeof radius === 'object') {
            const { tl, tr, bl, br } = radius
            if (tl !== undefined) css.borderTopLeftRadius = toCSS(tl)
            if (tr !== undefined) css.borderTopRightRadius = toCSS(tr)
            if (bl !== undefined) css.borderBottomLeftRadius = toCSS(bl)
            if (br !== undefined) css.borderBottomRightRadius = toCSS(br)
        } else {
            css.borderRadius = toCSS(radius)
        }
    }

    // Border Sides
    const borderStyles = getVal(borderSettings, 'styles', device)
    if (borderStyles) {
        const sides = ['top', 'right', 'bottom', 'left']
        sides.forEach(side => {
            const conf = borderStyles[side]
            if (conf) {
                const sideCap = side.charAt(0).toUpperCase() + side.slice(1)
                if (conf.width > 0 && conf.style !== 'none') {
                    css[`border${sideCap}Width`] = toCSS(conf.width)
                    css[`border${sideCap}Style`] = conf.style || 'solid'
                    css[`border${sideCap}Color`] = conf.color || 'transparent'
                }
            }
        })
    } else {
        const w = getVal(borderSettings, 'borderWidth', device)
        if (w !== undefined && w > 0) {
            const c = getVal(borderSettings, 'borderColor', device) || '#000'
            css.borderWidth = toCSS(w)
            css.borderStyle = 'solid'
            css.borderColor = c
        }
    }

    return css
}

export function getSpacingStyles(spacingSettings, type = 'padding', device = 'desktop') {
    if (!spacingSettings) return {}
    const css = {}

    const unit = getVal(spacingSettings, 'unit', device) || 'px'
    const top = getVal(spacingSettings, 'top', device)
    const bottom = getVal(spacingSettings, 'bottom', device)
    const left = getVal(spacingSettings, 'left', device)
    const right = getVal(spacingSettings, 'right', device)

    if (top !== undefined) css[`${type}Top`] = toCSS(top, unit)
    if (bottom !== undefined) css[`${type}Bottom`] = toCSS(bottom, unit)
    if (left !== undefined) css[`${type}Left`] = toCSS(left, unit)
    if (right !== undefined) css[`${type}Right`] = toCSS(right, unit)

    return css
}

export function getBoxShadowStyles(shadowSettings, device = 'desktop') {
    const s = resolve(shadowSettings, device);
    if (!s || s === 'none' || s.preset === 'none') return {}

    const { horizontal, vertical, blur, spread, color, inset } = s
    const h = horizontal || 0
    const v = vertical || 0
    const b = blur || 0
    const spr = spread || 0
    const c = color || 'rgba(0,0,0,0)'
    const i = inset ? 'inset ' : ''
    return { boxShadow: `${i}${h}px ${v}px ${b}px ${spr}px ${c}` }
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

export function getBackgroundStyles(settings, device = 'desktop') {
    if (!settings) return {}
    const css = {}
    const bgColor = getVal(settings, 'backgroundColor', device)
    if (bgColor) css.backgroundColor = bgColor

    const layers = []
    const sizes = []

    const gradientList = getVal(settings, 'backgroundGradients', device) || (getVal(settings, 'backgroundGradient', device) ? [getVal(settings, 'backgroundGradient', device)] : [])
    if (gradientList.length === 0 && getVal(settings, 'gradientStart', device)) {
        const start = getVal(settings, 'gradientStart', device)
        const end = getVal(settings, 'gradientEnd', device) || start
        const dir = getVal(settings, 'gradientDirection', device) || 'to right'
        layers.push(`linear-gradient(${dir}, ${start}, ${end})`)
        sizes.push('cover')
    }
    const gradientCSSList = gradientList.map(g => generateGradientCSS(g)).filter(c => !!c)

    const bgImage = getVal(settings, 'backgroundImage', device)
    const imageCSS = bgImage ? `url(${bgImage})` : ''
    let resolvedImageSize = ''
    if (imageCSS) {
        let size = getVal(settings, 'backgroundImageSize', device) || 'cover'
        if (size === 'custom') {
            const w = getVal(settings, 'backgroundImageWidth', device) || 'auto'
            const h = getVal(settings, 'backgroundImageHeight', device) || 'auto'
            size = `${toCSS(w)} ${toCSS(h)}`
        } else if (size === 'stretch') size = '100% 100%'
        else if (size === 'fit') size = 'contain'
        resolvedImageSize = size
    }

    if (getVal(settings, 'backgroundGradientShowAboveImage', device)) {
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

export function getSizingStyles(settings, device = 'desktop') {
    if (!settings) return {}
    const css = {}
    const width = getVal(settings, 'width', device)
    if (width && width !== 'auto') css.width = toCSS(width)
    const maxWidth = getVal(settings, 'maxWidth', device)
    if (maxWidth && maxWidth !== 'none') css.maxWidth = toCSS(maxWidth)
    const minWidth = getVal(settings, 'minWidth', device)
    if (minWidth) css.minWidth = toCSS(minWidth)
    const height = getVal(settings, 'height', device)
    if (height && height !== 'auto') css.height = toCSS(height)
    const minHeight = getVal(settings, 'minHeight', device)
    if (minHeight) css.minHeight = toCSS(minHeight)
    const maxHeight = getVal(settings, 'maxHeight', device)
    if (maxHeight && maxHeight !== 'none') css.maxHeight = toCSS(maxHeight)
    const overflow = getVal(settings, 'overflow', device)
    if (overflow && overflow !== 'visible') {
        css.overflow = overflow
        if (getVal(settings, 'overflowX', device)) css.overflowX = getVal(settings, 'overflowX', device)
        if (getVal(settings, 'overflowY', device)) css.overflowY = getVal(settings, 'overflowY', device)
    }
    const zIndex = getVal(settings, 'zIndex', device)
    if (zIndex !== undefined && zIndex !== null && zIndex !== '') css.zIndex = zIndex
    return css
}

export function getTransformStyles(settings, device = 'desktop') {
    if (!settings) return {}
    const transforms = []
    const origin = getVal(settings, 'transformOrigin', device) || getVal(settings, 'origin', device)
    const scale = getVal(settings, 'transformScale', device) || getVal(settings, 'scale', device)
    if (scale !== undefined && scale !== null && scale !== '' && scale != 100) transforms.push(`scale(${scale / 100})`)
    const tx = getVal(settings, 'translateX', device) || getVal(settings, 'transformTranslateX', device) || 0
    const ty = getVal(settings, 'translateY', device) || getVal(settings, 'transformTranslateY', device) || 0
    if (tx != 0 || ty != 0) transforms.push(`translate(${toCSS(tx)}, ${toCSS(ty)})`)
    const rx = getVal(settings, 'rotateX', device) || getVal(settings, 'transformRotateX', device) || 0
    const ry = getVal(settings, 'rotateY', device) || getVal(settings, 'transformRotateY', device) || 0
    const rz = getVal(settings, 'rotateZ', device) || getVal(settings, 'rotate', device) || getVal(settings, 'transformRotate', device) || 0
    if (rx != 0) transforms.push(`rotateX(${rx}deg)`)
    if (ry != 0) transforms.push(`rotateY(${ry}deg)`)
    if (rz != 0) transforms.push(`rotateZ(${rz}deg)`)
    const sx = getVal(settings, 'skewX', device) || getVal(settings, 'transformSkewX', device) || 0
    const sy = getVal(settings, 'skewY', device) || getVal(settings, 'transformSkewY', device) || 0
    if (sx != 0) transforms.push(`skewX(${sx}deg)`)
    if (sy != 0) transforms.push(`skewY(${sy}deg)`)
    const css = {}
    if (transforms.length > 0) css.transform = transforms.join(' ')
    if (origin && origin !== 'center') css.transformOrigin = origin
    return css
}
