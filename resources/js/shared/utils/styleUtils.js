import { BackgroundPatterns, BackgroundMasks } from '../../components/builder/core/AssetLibrary'

/**
 * Universal Style Utilities for both Builder (Editor) and Renderer (Frontend)
 */

export function getResponsiveValue(settings, baseKey, device = 'desktop') {
    if (device === 'desktop') return settings[baseKey]

    // Normalize device names (mobile, phone, tablet)
    const suffix = device === 'mobile' || device === 'phone' ? '_mobile' : `_${device}`
    const deviceValue = settings[baseKey + suffix]

    // Fallback: Phone/Mobile -> Tablet -> Desktop
    if (deviceValue !== undefined && deviceValue !== null && deviceValue !== '') return deviceValue

    if (device === 'mobile' || device === 'phone') {
        const tabletValue = settings[baseKey + '_tablet']
        if (tabletValue !== undefined && tabletValue !== null && tabletValue !== '') return tabletValue
    }

    return settings[baseKey]
}

/**
 * Robust value getter that handles:
 * 1. camelCase vs snake_case fallback
 * 2. Aliases (e.g. bgColor vs backgroundColor)
 * 3. Responsive objects (desktop/tablet/mobile)
 */
export function getVal(settings, key, device = 'desktop') {
    if (!settings) return undefined

    const aliases = {
        'backgroundColor': ['bgColor', 'background_color', 'color'],
        'backgroundImage': ['bgImage', 'background_image', 'image'],
        'backgroundSize': ['bgSize', 'background_size', 'size'],
        'backgroundPosition': ['bgPos', 'bgPosition', 'background_position', 'position'],
        'backgroundRepeat': ['bgRepeat', 'background_repeat', 'repeat'],
        'borderRadius': ['radius', 'r', 'border_radius'],
        'minHeight': ['min_height'],
        'maxHeight': ['max_height'],
        'minWidth': ['min_width'],
        'maxWidth': ['max_width'],
        'layoutType': ['layout_type'],
        'direction': ['flexDirection', 'flex_direction', 'grid_direction'],
        'flexWrap': ['flex_wrap', 'wrap'],
        'justifyContent': ['justify_content'],
        'alignItems': ['align_items'],
        'gapX': ['gap_x', 'columnGap', 'column_gap'],
        'gapY': ['gap_y', 'rowGap', 'row_gap'],
    }

    const tryKeys = [key]
    if (aliases[key]) tryKeys.push(...aliases[key])

    // Add snake_case and camelCase variants
    const snake = key.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`)
    const camel = key.replace(/_([a-z])/g, (g) => g[1].toUpperCase())
    if (!tryKeys.includes(snake)) tryKeys.push(snake)
    if (!tryKeys.includes(camel)) tryKeys.push(camel)

    for (const k of tryKeys) {
        const val = getResponsiveValue(settings, k, device)
        if (val !== undefined && val !== null && val !== '') return val
    }

    return undefined
}

export function toCSS(val, unit = 'px') {
    if (val === undefined || val === null || val === '' || typeof val === 'object') return undefined
    const s = String(val)
    if (s.includes('px') || s.includes('rem') || s.includes('%') || s.includes('vh') || s.includes('vw') || s === 'auto' || s === 'inherit') return s
    if (s.match(/^-?\d*\.?\d+$/)) return `${s}${unit}`
    return s
}

export function getTypographyStyles(settings, prefix = '', device = 'desktop') {
    const css = {}
    const fields = [
        { key: 'font_family', prop: 'fontFamily' },
        { key: 'font_size', prop: 'fontSize', unit: 'px' },
        { key: 'line_height', prop: 'lineHeight' },
        { key: 'letter_spacing', prop: 'letterSpacing', unit: 'px' },
        { key: 'text_color', prop: 'color' },
        { key: 'font_weight', prop: 'fontWeight' },
        { key: 'font_style', prop: 'fontStyle' },
        { key: 'text_align', prop: 'textAlign' },
        { key: 'text_transform', prop: 'textTransform' },
        { key: 'text_decoration', prop: 'textDecoration' }
    ]

    fields.forEach(f => {
        const fullKey = prefix ? `${prefix}_${f.key}` : f.key
        let val = getVal(settings, fullKey, device)
        if (val !== undefined && val !== null && val !== '') {
            css[f.prop] = f.unit ? toCSS(val, f.unit) : val
        }
    })

    return css
}

export function getSpacingStyles(settings, baseKey, device = 'desktop', type = 'padding') {
    const value = getResponsiveValue(settings, baseKey, device)
    if (!value) return {}

    const css = {}
    const { top, bottom, left, right, unit = 'px' } = value

    if (top !== undefined) css[`${type}Top`] = toCSS(top, unit)
    if (bottom !== undefined) css[`${type}Bottom`] = toCSS(bottom, unit)
    if (left !== undefined) css[`${type}Left`] = toCSS(left, unit)
    if (right !== undefined) css[`${type}Right`] = toCSS(right, unit)

    return css
}

export function getBorderStyles(settings, baseKey = 'border', device = 'desktop') {
    const borderSettings = getResponsiveValue(settings, baseKey, device)
    if (!borderSettings) return {}
    const css = {}

    // Radius
    const radius = borderSettings.radius
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
    if (borderSettings.styles) {
        const sides = ['top', 'right', 'bottom', 'left']
        sides.forEach(side => {
            const conf = borderSettings.styles[side]
            if (conf && conf.width > 0 && conf.style !== 'none') {
                const sideCap = side.charAt(0).toUpperCase() + side.slice(1)
                css[`border${sideCap}Width`] = toCSS(conf.width)
                css[`border${sideCap}Style`] = conf.style || 'solid'
                css[`border${sideCap}Color`] = conf.color || 'transparent'
            }
        })
    }

    return css
}

export function getBoxShadowStyles(settings, baseKey = 'boxShadow', device = 'desktop') {
    const s = getResponsiveValue(settings, baseKey, device)
    if (!s || s === 'none' || s.preset === 'none') return {}

    const { horizontal = 0, vertical = 0, blur = 0, spread = 0, color = 'rgba(0,0,0,0)', inset } = s
    const i = inset ? 'inset ' : ''
    return { boxShadow: `${i}${horizontal}px ${vertical}px ${blur}px ${spread}px ${color}` }
}

export function getSizingStyles(settings, device = 'desktop') {
    const css = {}
    const props = ['width', 'maxWidth', 'minWidth', 'height', 'maxHeight', 'minHeight', 'zIndex']

    props.forEach(p => {
        const val = getVal(settings, p, device)
        if (val !== undefined && val !== null && val !== '') {
            css[p] = (p === 'zIndex') ? val : toCSS(val)
        }
    })

    // Special alignment logic
    const align = getVal(settings, 'align', device)
    if (align === 'center') { css.marginLeft = 'auto'; css.marginRight = 'auto' }
    else if (align === 'left') { css.marginLeft = '0'; css.marginRight = 'auto' }
    else if (align === 'right') { css.marginLeft = 'auto'; css.marginRight = '0' }

    return css
}

export function getBackgroundStyles(settings, device = 'desktop') {
    if (!settings) return {}
    const css = {}

    const bgColor = getVal(settings, 'backgroundColor', device)
    if (bgColor) css.backgroundColor = bgColor

    const layers = []
    const sizes = []
    const repeatList = []
    const posList = []
    const blendList = []

    // 1. Gradients
    const gradientList = getVal(settings, 'backgroundGradients', device) || []
    const gradientCSSList = gradientList.map(g => {
        if (!g || !g.stops || g.stops.length < 2) return ''
        const { type = 'linear', direction = '180deg', stops, repeat = false } = g
        const prefix = repeat ? 'repeating-' : ''
        const sortedStops = [...stops].sort((a, b) => a.position - b.position)
        const stopString = sortedStops.map(s => `${s.color} ${s.position}%`).join(', ')
        if (type === 'linear') return `${prefix}linear-gradient(${direction}, ${stopString})`
        return `${prefix}radial-gradient(circle, ${stopString})`
    }).filter(c => !!c)

    // 2. Image
    const bgImage = getVal(settings, 'backgroundImage', device)
    const imageCSS = bgImage ? `url(${bgImage})` : ''

    // Assemble layers
    if (getVal(settings, 'backgroundGradientShowAboveImage', device)) {
        gradientCSSList.forEach(g => { layers.push(g); sizes.push('100% 100%'); repeatList.push('no-repeat'); posList.push('center'); blendList.push('normal') })
        if (imageCSS) { layers.push(imageCSS); sizes.push(getVal(settings, 'backgroundSize', device) || 'cover'); repeatList.push('no-repeat'); posList.push('center'); blendList.push('normal') }
    } else {
        if (imageCSS) { layers.push(imageCSS); sizes.push(getVal(settings, 'backgroundSize', device) || 'cover'); repeatList.push('no-repeat'); posList.push('center'); blendList.push('normal') }
        gradientCSSList.forEach(g => { layers.push(g); sizes.push('100% 100%'); repeatList.push('no-repeat'); posList.push('center'); blendList.push('normal') })
    }

    if (layers.length > 0) {
        css.backgroundImage = layers.join(', ')
        css.backgroundSize = sizes.join(', ')
        css.backgroundRepeat = repeatList.join(', ')
        css.backgroundPosition = posList.join(', ')
        css.backgroundBlendMode = blendList.join(', ')
    }

    return css
}

export function getLayoutStyles(settings, device = 'desktop') {
    const css = {}
    const layoutType = getVal(settings, 'layoutType', device)

    if (layoutType === 'flex') {
        css.display = 'flex'
        css.flexDirection = getVal(settings, 'direction', device) || 'column'
        css.flexWrap = getVal(settings, 'flexWrap', device) || 'nowrap'
        css.justifyContent = getVal(settings, 'justifyContent', device) || 'flex-start'
        css.alignItems = getVal(settings, 'alignItems', device) || 'stretch'

        const gapX = toCSS(getVal(settings, 'gapX', device))
        const gapY = toCSS(getVal(settings, 'gapY', device))
        if (gapX && gapY) css.gap = `${gapY} ${gapX}`
        else if (gapX) css.columnGap = gapX
        else if (gapY) css.rowGap = gapY

    } else if (layoutType === 'grid') {
        css.display = 'grid'
        const cols = getVal(settings, 'columnCount', device) || 3
        css.gridTemplateColumns = cols === 'auto' ? `repeat(auto-fit, minmax(0, 1fr))` : `repeat(${cols}, 1fr)`

        const gapX = toCSS(getVal(settings, 'gapX', device))
        const gapY = toCSS(getVal(settings, 'gapY', device))
        if (gapX && gapY) css.gap = `${gapY} ${gapX}`

        css.justifyContent = getVal(settings, 'justifyContent', device)
        css.alignItems = getVal(settings, 'alignItems', device)
    }

    return css
}

export function getFilterStyles(settings, device = 'desktop') {
    const css = {}
    const filters = []

    const getFilterVal = (key) => {
        const nested = getResponsiveValue(settings, 'filter', device)
        if (nested && nested[key] !== undefined) return nested[key]
        return getVal(settings, key, device)
    }

    const opacity = getFilterVal('opacity')
    if (opacity !== undefined && opacity != 100) css.opacity = opacity / 100

    const blendMode = getFilterVal('blendMode') || getFilterVal('blend_mode')
    if (blendMode && blendMode !== 'normal') css.mixBlendMode = blendMode

    const blur = getFilterVal('blur')
    if (blur && blur > 0) filters.push(`blur(${blur}px)`)

    const brightness = getFilterVal('brightness')
    if (brightness !== undefined && brightness != 100) filters.push(`brightness(${brightness}%)`)

    const contrast = getFilterVal('contrast')
    if (contrast !== undefined && contrast != 100) filters.push(`contrast(${contrast}%)`)

    const grayscale = getFilterVal('grayscale')
    if (grayscale && grayscale > 0) filters.push(`grayscale(${grayscale}%)`)

    const sepia = getFilterVal('sepia')
    if (sepia && sepia > 0) filters.push(`sepia(${sepia}%)`)

    const preserveSaturate = getFilterVal('saturate')
    if (preserveSaturate !== undefined && preserveSaturate != 100) filters.push(`saturate(${preserveSaturate}%)`)

    const hueRotate = getFilterVal('hueRotate') || getFilterVal('hue_rotate')
    if (hueRotate && hueRotate > 0) filters.push(`hue-rotate(${hueRotate}deg)`)

    const invert = getFilterVal('invert')
    if (invert && invert > 0) filters.push(`invert(${invert}%)`)

    if (filters.length > 0) css.filter = filters.join(' ')
    return css
}

export function getTransformStyles(settings, device = 'desktop') {
    const transforms = []
    const getTransVal = (key) => {
        const nested = getResponsiveValue(settings, 'transform', device)
        if (nested && nested[key] !== undefined) return nested[key]
        return getVal(settings, key, device)
    }

    const scale = getTransVal('scale') ?? getTransVal('transformScale') ?? getTransVal('transform_scale')
    if (scale !== undefined && scale != 100) transforms.push(`scale(${scale / 100})`)

    const tx = getTransVal('translateX') ?? getTransVal('translate_x') ?? getTransVal('transform_translate_x')
    const ty = getTransVal('translateY') ?? getTransVal('translate_y') ?? getTransVal('transform_translate_y')
    if ((tx && tx != 0) || (ty && ty != 0)) transforms.push(`translate(${toCSS(tx)}, ${toCSS(ty)})`)

    const rx = getTransVal('rotateX') ?? getTransVal('transform_rotate') ?? 0
    const ry = getTransVal('rotateY') ?? getTransVal('transform_rotate_y') ?? 0
    const rz = getTransVal('rotateZ') ?? getTransVal('rotate') ?? getTransVal('transform_rotate_z') ?? 0

    if (rx && rx != 0) transforms.push(`rotateX(${rx}deg)`)
    if (ry && ry != 0) transforms.push(`rotateY(${ry}deg)`)
    if (rz && rz != 0) transforms.push(`rotateZ(${rz}deg)`)

    const sx = getTransVal('skewX') ?? getTransVal('transform_skew_x') ?? 0
    const sy = getTransVal('skewY') ?? getTransVal('transform_skew_y') ?? 0
    if (sx && sx != 0) transforms.push(`skewX(${sx}deg)`)
    if (sy && sy != 0) transforms.push(`skewY(${sy}deg)`)

    const css = {}
    if (transforms.length > 0) css.transform = transforms.join(' ')
    const origin = getTransVal('origin') ?? getTransVal('transformOrigin')
    if (origin && origin !== 'center') css.transformOrigin = origin

    return css
}

export function hexToHsl(hex) {
    hex = hex.replace('#', '')
    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('')

    const r = parseInt(hex.substring(0, 2), 16) / 255
    const g = parseInt(hex.substring(2, 4), 16) / 255
    const b = parseInt(hex.substring(4, 6), 16) / 255

    const max = Math.max(r, g, b), min = Math.min(r, g, b)
    let h, s, l = (max + min) / 2

    if (max === min) {
        h = s = 0
    } else {
        const d = max - min
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min)
        switch (max) {
            case r: h = (g - b) / d + (g < b ? 6 : 0); break
            case g: h = (b - r) / d + 2; break
            case b: h = (r - g) / d + 4; break
        }
        h /= 6
    }

    return { h: h * 360, s: s * 100, l: l * 100 }
}

export function hslToHex(h, s, l) {
    h /= 360; s /= 100; l /= 100
    let r, g, b
    if (s === 0) {
        r = g = b = l
    } else {
        const hue2rgb = (p, q, t) => {
            if (t < 0) t += 1
            if (t > 1) t -= 1
            if (t < 1 / 6) return p + (q - p) * 6 * t
            if (t < 1 / 2) return q
            if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6
            return p
        }
        const q = l < 0.5 ? l * (1 + s) : l + s - l * s
        const p = 2 * l - q
        r = hue2rgb(p, q, h + 1 / 3)
        g = hue2rgb(p, q, h)
        b = hue2rgb(p, q, h - 1 / 3)
    }
    const toHex = x => {
        const hex = Math.round(x * 255).toString(16)
        return hex.length === 1 ? '0' + hex : hex
    }
    return `#${toHex(r)}${toHex(g)}${toHex(b)}`
}

export function getHarmoniousGradientColors(baseHex) {
    const { h, s, l } = hexToHsl(baseHex)

    // Modern "Kekinian" logic: shift hue slightly and adjust brightness
    const secondaryHue = (h + 25) % 360
    const secondaryL = l > 60 ? l - 20 : l + 20
    const secondaryS = Math.min(100, s + 10)

    return [
        baseHex,
        hslToHex(secondaryHue, secondaryS, secondaryL)
    ]
}

export function getAnimationStyles(settings, device = 'desktop') {
    const css = {}

    // Support both old flat keys and new nested object
    const getAnimVal = (key) => {
        // 1. Try nested object first
        const nested = getResponsiveValue(settings, 'animation', device)
        if (nested && nested[key] !== undefined) return nested[key]

        // 2. Fallback to flat key (prefixed with animation_)
        return getResponsiveValue(settings, `animation_${key}`, device)
    }

    const effect = getAnimVal('effect')

    if (effect) {
        const duration = getAnimVal('duration')
        const delay = getAnimVal('delay')
        const repeat = getAnimVal('repeat') || 'once'
        const curve = getAnimVal('curve')

        if (duration !== undefined && duration !== null) css.animationDuration = `${duration}ms`
        if (delay !== undefined && delay !== null) css.animationDelay = `${delay}ms`
        if (curve) css.animationTimingFunction = curve

        if (repeat === 'infinite') {
            css.animationIterationCount = 'infinite'
        } else if (repeat === 'once' || repeat === '1') {
            css.animationIterationCount = '1'
        }
    }

    return css
}

export function getVisibilityStyles(settings, device = 'desktop') {
    const visibility = settings.visibility
    if (!visibility) return {}

    const desktop = visibility.desktop !== false
    const tablet = visibility.tablet !== undefined ? visibility.tablet !== false : desktop
    const mobile = visibility.mobile !== undefined ? visibility.mobile !== false : tablet

    let isVisible = true
    if (device === 'desktop') isVisible = desktop
    else if (device === 'tablet') isVisible = tablet
    else if (device === 'mobile') isVisible = mobile

    return isVisible ? {} : { display: 'none' }
}

export function getPositioningStyles(settings, device = 'desktop') {
    const styles = {}
    const zIndex = getResponsiveValue(settings, 'zIndex', device)

    if (zIndex !== undefined && zIndex !== '' && zIndex !== 0) {
        styles.zIndex = zIndex
    }

    const pos = getResponsiveValue(settings, 'position', device)
    if (pos === 'sticky' || pos === 'fixed') {
        styles.position = pos
        const top = getResponsiveValue(settings, 'stickyTop', device)
        if (top !== undefined) styles.top = toCSS(top)
    } else if (pos === 'relative' || pos === 'absolute') {
        styles.position = pos
        const top = getResponsiveValue(settings, 'top', device); if (top) styles.top = toCSS(top)
        const bottom = getResponsiveValue(settings, 'bottom', device); if (bottom) styles.bottom = toCSS(bottom)
        const left = getResponsiveValue(settings, 'left', device); if (left) styles.left = toCSS(left)
        const right = getResponsiveValue(settings, 'right', device); if (right) styles.right = toCSS(right)
    }

    return styles
}

export function getColorVariables(settings, hoverSettings = {}, device = 'desktop') {
    // Return CSS variables for colors to support hover via consistent class names or inline vars
    // We assume the component or a global utility class will handle the :hover state using these vars
    // e.g. .ja-hover-support:hover { color: var(--hover-text-color); background-color: var(--hover-bg-color); }

    const styles = {}

    // Check for textColor or color (legacy/alt names)
    const textColor = getVal(settings, 'textColor', device)
    const hoverTextColor = getVal(hoverSettings, 'textColor', device)

    const bgColor = getVal(settings, 'backgroundColor', device)
    const hoverBgColor = getVal(hoverSettings, 'backgroundColor', device)

    if (textColor) styles['--text-color'] = textColor
    if (hoverTextColor) styles['--hover-text-color'] = hoverTextColor

    if (bgColor) styles['--bg-color'] = bgColor
    if (hoverBgColor) styles['--hover-bg-color'] = hoverBgColor

    return styles
}
