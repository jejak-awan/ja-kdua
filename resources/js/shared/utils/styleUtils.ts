import { BackgroundPatterns, BackgroundMasks } from './AssetLibrary'
import type { ModuleSettings } from '../../types/builder'

/**
 * Universal Style Utilities for both Builder (Editor) and Renderer (Frontend)
 */

export function getResponsiveValue(settings: ModuleSettings, baseKey: string, device: string = 'desktop'): any {
    if (!settings) return undefined
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

export const generateGradientCSS = (gradient: any) => {
    if (!gradient || !gradient.stops || gradient.stops.length < 2) return ''
    const stops = [...gradient.stops]
        .sort((a, b) => a.position - b.position)
        .map(s => `${s.color} ${s.position}%`)
        .join(', ')
    return `linear-gradient(${gradient.direction || '180deg'}, ${stops})`
}

/**
 * Robust value getter that handles:
 * 1. camelCase vs snake_case fallback
 * 2. Aliases (e.g. bgColor vs backgroundColor)
 * 3. Responsive objects (desktop/tablet/mobile)
 */
export function getVal(settings: ModuleSettings, key: string, device: string = 'desktop'): any {
    if (!settings) return undefined

    const aliases: Record<string, string[]> = {
        'backgroundColor': ['bgColor', 'background_color'],
        'text_color': ['color', 'textColor'],
        'text_shadow': ['textShadow'],
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
        'displayStyle': ['display_style', 'style'],
        'direction': ['flexDirection', 'flex_direction', 'grid_direction'],
        'flexWrap': ['flex_wrap', 'wrap'],
        'justifyContent': ['justify_content'],
        'alignItems': ['align_items'],
        'gapX': ['gap_x', 'columnGap', 'column_gap'],
        'gapY': ['gap_y', 'rowGap', 'row_gap'],
        'fontSize': ['font_size', 'size'],
        'fontWeight': ['font_weight', 'weight'],
        'lineHeight': ['line_height'],
        'letterSpacing': ['letter_spacing'],
        'textAlign': ['text_align', 'alignment', 'align'],
        'textDecoration': ['text_decoration'],
        'textTransform': ['text_transform'],
        'fontFamily': ['font_family', 'font'],
        'fontStyle': ['font_style', 'style'],
        'verticalAlign': ['vertical_align', 'vAlign'],
        'value': ['percentage', 'percent', 'progress'],
    }

    const tryKeys = [key]
    if (aliases[key]) tryKeys.push(...aliases[key])

    // Support prefixed aliases (e.g. title_text_color -> title_color)
    for (const [baseKey, propAliases] of Object.entries(aliases)) {
        if (key.endsWith(`_${baseKey}`)) {
            const prefix = key.substring(0, key.length - baseKey.length - 1)
            propAliases.forEach(a => {
                const aliasedKey = `${prefix}_${a}`
                if (!tryKeys.includes(aliasedKey)) tryKeys.push(aliasedKey)
            })
        }
    }

    // Add snake_case and camelCase variants
    const snake = key.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`)
    const camel = key.replace(/_([a-z])/g, (g) => g[1].toUpperCase())
    if (!tryKeys.includes(snake)) tryKeys.push(snake)
    if (!tryKeys.includes(camel)) tryKeys.push(camel)

    // Also add camel/snake variants for aliases? 
    const originalTryKeys = [...tryKeys]
    originalTryKeys.forEach(k => {
        const s = k.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`)
        const c = k.replace(/_([a-z])/g, (g) => g[1].toUpperCase())
        if (!tryKeys.includes(s)) tryKeys.push(s)
        if (!tryKeys.includes(c)) tryKeys.push(c)
    })

    for (const k of tryKeys) {
        const val = getResponsiveValue(settings, k, device)
        if (val !== undefined && val !== null && val !== '') return val
    }

    // Secondary Check: Nested objects (e.g. background.color)
    const backgroundProps: Record<string, string> = {
        'backgroundColor': 'color',
        'backgroundImage': 'image',
        'backgroundPosition': 'position',
        'backgroundRepeat': 'repeat',
        'backgroundSize': 'size'
    }

    const bgObj = settings.background
    if (bgObj && typeof bgObj === 'object') {
        for (const k of tryKeys) {
            if (backgroundProps[k]) {
                const subKey = backgroundProps[k]
                const val = getResponsiveValue(bgObj, subKey, device)
                if (val !== undefined && val !== null && val !== '') return val
            }
        }
    }

    return undefined
}

export function toCSS(val: any, unit: string = 'px'): string | undefined {
    if (val === undefined || val === null || val === '' || typeof val === 'object') return undefined
    const s = String(val)
    if (s.includes('px') || s.includes('rem') || s.includes('em') || s.includes('%') || s.includes('vh') || s.includes('vw') || s.includes('calc') || s.includes('var') || s === 'auto' || s === 'inherit') return s
    if (s.match(/^-?\d*\.?\d+$/)) return `${s}${unit}`
    return s
}

export function getTypographyStyles(settings: ModuleSettings, prefix: string = '', device: string = 'desktop'): Record<string, any> {
    const css: Record<string, any> = {}
    const fields = [
        { key: 'font_family', prop: 'fontFamily' },
        { key: 'font_size', prop: 'fontSize', unit: 'px' },
        { key: 'line_height', prop: 'lineHeight', unit: 'em' },
        { key: 'letter_spacing', prop: 'letterSpacing', unit: 'px' },
        { key: 'text_color', prop: 'color' },
        { key: 'font_weight', prop: 'fontWeight' },
        { key: 'font_style', prop: 'fontStyle' },
        { key: 'text_align', prop: 'textAlign' },
        { key: 'text_transform', prop: 'textTransform' },
        { key: 'text_decoration', prop: 'textDecoration' },
        { key: 'text_shadow', prop: 'textShadow' }
    ]

    fields.forEach(f => {
        const sep = (prefix && !prefix.endsWith('_')) ? '_' : ''
        const fullKey = prefix ? `${prefix}${sep}${f.key}` : f.key
        const val = getVal(settings, fullKey, device)
        if (val !== undefined && val !== null && val !== '') {
            if (f.key === 'text_shadow') {
                const shadowStyles = getTextShadowStyles(settings, fullKey, device)
                if (shadowStyles.textShadow) css.textShadow = shadowStyles.textShadow
            } else {
                css[f.prop] = f.unit ? toCSS(val, f.unit) : val
            }
        }
    })

    return css
}

export function getSpacingStyles(settings: ModuleSettings, baseKey: string, device: string = 'desktop', type: string = 'padding'): Record<string, any> {
    const value = getResponsiveValue(settings, baseKey, device)
    if (!value) return {}

    const css: Record<string, any> = {}
    const { top, bottom, left, right, unit = 'px' } = value

    if (top !== undefined) css[`${type}Top`] = toCSS(top, unit)
    if (bottom !== undefined) css[`${type}Bottom`] = toCSS(bottom, unit)
    if (left !== undefined) css[`${type}Left`] = toCSS(left, unit)
    if (right !== undefined) css[`${type}Right`] = toCSS(right, unit)

    return css
}

export function getBorderStyles(settings: ModuleSettings, baseKey: string = 'border', device: string = 'desktop'): Record<string, any> {
    const borderSettings = getResponsiveValue(settings, baseKey, device)
    if (!borderSettings) return {}
    const css: Record<string, any> = {}

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

export function getBoxShadowStyles(settings: ModuleSettings, baseKey: string = 'boxShadow', device: string = 'desktop'): Record<string, any> {
    const s = getResponsiveValue(settings, baseKey, device)
    if (!s || s === 'none' || s.preset === 'none') return {}

    const { horizontal = 0, vertical = 0, blur = 0, spread = 0, color = 'rgba(0,0,0,0)', inset } = s
    const i = inset ? 'inset ' : ''
    return { boxShadow: `${i}${horizontal}px ${vertical}px ${blur}px ${spread}px ${color}` }
}

export function getTextShadowStyles(settings: ModuleSettings, baseKey: string = 'text_shadow', device: string = 'desktop'): Record<string, any> {
    const s = getResponsiveValue(settings, baseKey, device)
    if (!s || s === 'none' || s.preset === 'none') return {}

    // text-shadow does NOT support spread or inset
    const { horizontal = 0, vertical = 0, blur = 0, color = 'rgba(0,0,0,0)' } = s
    return { textShadow: `${horizontal}px ${vertical}px ${blur}px ${color}` }
}

export function getSizingStyles(settings: ModuleSettings, device: string = 'desktop'): Record<string, any> {
    const css: Record<string, any> = {}
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

const svgToDataUri = (svgStr: string): string => {
    const encoded = encodeURIComponent(svgStr)
        .replace(/'/g, '%27')
        .replace(/"/g, '%22')
    return `data:image/svg+xml;charset=utf-8,${encoded}`
}

export function getBackgroundStyles(settings: ModuleSettings, device: string = 'desktop'): Record<string, any> {
    if (!settings) return {}
    const css: Record<string, any> = {}

    const bgColor = getVal(settings, 'backgroundColor', device)
    if (bgColor) css.backgroundColor = bgColor

    const layers: string[] = []
    const sizes: string[] = []
    const repeatList: string[] = []
    const posList: string[] = []
    const blendList: string[] = []

    // --- 0. Pattern (Top Layer) ---
    const patternId = getVal(settings, 'backgroundPattern', device)
    if (patternId && patternId !== 'none') {
        const patternObj = BackgroundPatterns.find((p: any) => p.id === patternId)
        if (patternObj) {
            const rawSvg = patternObj.svg
            // Resolve object structure if necessary
            let svg: string = typeof rawSvg === 'object'
                ? ((rawSvg as any).default || (rawSvg as any).regular || '')
                : (rawSvg as string)

            if (svg) {
                // Wrap in full SVG tag if it's just a fragment
                if (!svg.startsWith('<svg')) {
                    const w = patternObj.width || 100
                    const h = patternObj.height || 100
                    svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" viewBox="0 0 ${w} ${h}">${svg}</svg>`
                }

                // Pattern Color
                const pColor = getVal(settings, 'backgroundPatternColor', device)
                if (pColor) {
                    svg = svg.replace(/fill="currentColor"/g, `fill="${pColor}"`)
                        .replace(/stroke="currentColor"/g, `stroke="${pColor}"`)
                        .replace(/fill="[^"]*"/g, `fill="${pColor}"`)
                        .replace(/stroke="[^"]*"/g, `stroke="${pColor}"`)
                }

                layers.push(`url("${svgToDataUri(svg)}")`)

                const pSize = getVal(settings, 'backgroundPatternSize', device) || 'auto'
                const pWidth = getVal(settings, 'backgroundPatternWidth', device)
                const pHeight = getVal(settings, 'backgroundPatternHeight', device)

                if (pSize === 'custom' && pWidth && pHeight) {
                    sizes.push(`${toCSS(pWidth)} ${toCSS(pHeight)}`)
                } else if (pSize === 'cover' || pSize === 'contain') {
                    sizes.push(pSize)
                } else {
                    sizes.push('auto')
                }

                const pRepeat = getVal(settings, 'backgroundPatternRepeat', device) || 'repeat'
                repeatList.push(pRepeat)

                // Pattern Position (Offset via position)
                posList.push('0 0')

                const pBlend = getVal(settings, 'backgroundPatternBlendMode', device) || 'normal'
                blendList.push(pBlend)
            }
        }
    }

    // --- 1. Gradients ---
    const gradientList = getVal(settings, 'backgroundGradients', device) || []
    const singleGradient = getVal(settings, 'backgroundGradient', device)
    let finalGradientList = []

    if (gradientList.length > 0) {
        finalGradientList = gradientList
    } else if (singleGradient) {
        finalGradientList = [singleGradient]
    }

    const gradientCSSList = finalGradientList.map((g: any) => {
        if (!g || !g.stops || g.stops.length < 2) return ''
        const { type = 'linear', direction = '180deg', stops, repeat = false } = g
        const prefix = repeat ? 'repeating-' : ''
        const sortedStops = [...stops].sort((a, b) => a.position - b.position)
        const stopString = sortedStops.map(s => `${s.color} ${s.position}%`).join(', ')
        if (type === 'linear') return `${prefix}linear-gradient(${direction}, ${stopString})`
        return `${prefix}radial-gradient(circle, ${stopString})`
    }).filter((c: any) => !!c)

    // --- 2. Image ---
    const bgImage = getVal(settings, 'backgroundImage', device)
    const imageCSS = bgImage ? `url("${bgImage}")` : ''

    if (getVal(settings, 'backgroundGradientShowAboveImage', device)) {
        gradientCSSList.forEach((g: any) => { layers.push(g as string); sizes.push('100% 100%'); repeatList.push('no-repeat'); posList.push('center'); blendList.push('normal') })
        if (imageCSS) {
            layers.push(imageCSS);
            sizes.push(getVal(settings, 'backgroundImageSize', device) || 'cover');
            repeatList.push(getVal(settings, 'backgroundImageRepeat', device) || 'no-repeat');
            posList.push(getVal(settings, 'backgroundImagePosition', device) || 'center');
            blendList.push(getVal(settings, 'backgroundImageBlendMode', device) || 'normal')
        }
    } else {
        if (imageCSS) {
            layers.push(imageCSS);
            sizes.push(getVal(settings, 'backgroundImageSize', device) || 'cover');
            repeatList.push(getVal(settings, 'backgroundImageRepeat', device) || 'no-repeat');
            posList.push(getVal(settings, 'backgroundImagePosition', device) || 'center');
            blendList.push(getVal(settings, 'backgroundImageBlendMode', device) || 'normal')
        }
        gradientCSSList.forEach((g: any) => { layers.push(g as string); sizes.push('100% 100%'); repeatList.push('no-repeat'); posList.push('center'); blendList.push('normal') })
    }

    if (layers.length > 0) {
        css.backgroundImage = layers.join(', ')
        css.backgroundSize = sizes.join(', ')
        css.backgroundRepeat = repeatList.join(', ')
        css.backgroundPosition = posList.join(', ')
        css.backgroundBlendMode = blendList.join(', ')
    }

    // --- 3. Mask ---
    const maskId = getVal(settings, 'backgroundMask', device)
    if (maskId && maskId !== 'none') {
        const maskObj = BackgroundMasks.find((m: any) => m.id === maskId)
        if (maskObj) {
            const variant = device === 'mobile' || device === 'phone' ? 'portrait' : (device === 'tablet' ? 'square' : 'landscape')
            const rawSvgSet = maskObj.svg?.default || maskObj.svg
            let maskSvg = typeof rawSvgSet === 'object' ? ((rawSvgSet as any)[variant] || (rawSvgSet as any).landscape || (rawSvgSet as any).default || '') : (rawSvgSet as string)

            if (maskSvg) {
                if (!maskSvg.startsWith('<svg')) {
                    const vb = (maskObj as any).viewBox?.[variant] || (maskObj as any).viewBox?.landscape || '0 0 100 100'
                    const safeSvg = maskSvg.replace(/currentColor/g, 'black')
                    maskSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${vb}" width="100%" height="100%" preserveAspectRatio="none">${safeSvg}</svg>`
                } else {
                    maskSvg = maskSvg.replace(/currentColor/g, 'black')
                }

                const maskUri = `url("${svgToDataUri(maskSvg)}")`
                css.maskImage = maskUri
                css.webkitMaskImage = maskUri

                const mSize = getVal(settings, 'backgroundMaskSize', device) || 'fit'
                let sizeVal = 'contain'
                if (mSize === 'custom') {
                    const mW = getVal(settings, 'backgroundMaskWidth', device)
                    const mH = getVal(settings, 'backgroundMaskHeight', device)
                    sizeVal = (mW && mH) ? `${toCSS(mW)} ${toCSS(mH)}` : 'contain'
                } else if (mSize === 'fit') {
                    sizeVal = 'contain'
                } else if (mSize === 'fill') {
                    sizeVal = '100% 100%'
                } else {
                    sizeVal = mSize
                }

                css.maskSize = sizeVal
                css.webkitMaskSize = sizeVal
                css.maskPosition = 'center'
                css.webkitMaskPosition = 'center'
                css.maskRepeat = 'no-repeat'
                css.webkitMaskRepeat = 'no-repeat'
            }
        }
    }

    return css
}

export function getLayoutStyles(settings: ModuleSettings, device: string = 'desktop'): Record<string, any> {
    const css: Record<string, any> = {}
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

export function getFilterStyles(settings: ModuleSettings, device: string = 'desktop'): Record<string, any> {
    const css: Record<string, any> = {}
    const filters: string[] = []

    const getFilterVal = (key: string) => {
        const nested = getResponsiveValue(settings, 'filter', device)
        if (nested) {
            if (nested[key] !== undefined) return nested[key]
            const snake = key.replace(/[A-Z]/g, l => `_${l.toLowerCase()}`)
            if (nested[snake] !== undefined) return nested[snake]
        }
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

export function getTransformStyles(settings: ModuleSettings, device: string = 'desktop'): Record<string, any> {
    const transforms: string[] = []
    const getTransVal = (key: string) => {
        const nested = getResponsiveValue(settings, 'transform', device)
        if (nested) {
            if (nested[key] !== undefined) return nested[key]
            const snake = key.replace(/[A-Z]/g, l => `_${l.toLowerCase()}`)
            if (nested[snake] !== undefined) return nested[snake]
        }
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

    const css: Record<string, any> = {}
    if (transforms.length > 0) css.transform = transforms.join(' ')
    const origin = getTransVal('origin') ?? getTransVal('transformOrigin')
    if (origin && origin !== 'center') css.transformOrigin = origin

    return css
}

export function hexToHsl(hex: string): { h: number; s: number; l: number } {
    hex = hex.replace('#', '')
    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('')

    const r = parseInt(hex.substring(0, 2), 16) / 255
    const g = parseInt(hex.substring(2, 4), 16) / 255
    const b = parseInt(hex.substring(4, 6), 16) / 255

    const max = Math.max(r, g, b), min = Math.min(r, g, b)
    const l: number = (max + min) / 2
    let h: number, s: number

    if (max === min) {
        h = s = 0
    } else {
        const d = max - min
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min)
        switch (max) {
            case r: h = (g - b) / d + (g < b ? 6 : 0); break
            case g: h = (b - r) / d + 2; break
            case b: h = (r - g) / d + 4; break
            default: h = 0;
        }
        h /= 6
    }

    return { h: h * 360, s: s * 100, l: l * 100 }
}

export function hslToHex(h: number, s: number, l: number): string {
    h /= 360; s /= 100; l /= 100
    let r: number, g: number, b: number
    if (s === 0) {
        r = g = b = l
    } else {
        const hue2rgb = (p: number, q: number, t: number) => {
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
    const toHex = (x: number) => {
        const hex = Math.round(x * 255).toString(16)
        return hex.length === 1 ? '0' + hex : hex
    }
    return `#${toHex(r)}${toHex(g)}${toHex(b)}`
}

export function getHarmoniousGradientColors(baseHex: string): [string, string] {
    const { h, s, l } = hexToHsl(baseHex)

    const secondaryHue = (h + 25) % 360
    const secondaryL = l > 60 ? l - 20 : l + 20
    const secondaryS = Math.min(100, s + 10)

    return [
        baseHex,
        hslToHex(secondaryHue, secondaryS, secondaryL)
    ]
}

export function getAnimationStyles(settings: ModuleSettings, device: string = 'desktop'): Record<string, any> {
    const css: Record<string, any> = {}

    const getAnimVal = (key: string) => {
        const nested = getResponsiveValue(settings, 'animation', device)
        if (nested && nested[key] !== undefined) return nested[key]
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

export function getVisibilityStyles(settings: ModuleSettings, device: string = 'desktop'): Record<string, any> {
    const css: Record<string, any> = {}

    const visibility = settings.visibility
    if (visibility && !Array.isArray(getVal(settings, 'disable_on', device))) {
        const desktop = visibility.desktop !== false
        const tablet = visibility.tablet !== undefined ? visibility.tablet !== false : desktop
        const mobile = visibility.mobile !== undefined ? visibility.mobile !== false : tablet

        let isVisible = true
        if (device === 'desktop') isVisible = desktop
        else if (device === 'tablet') isVisible = tablet
        else if (device === 'mobile') isVisible = mobile

        if (!isVisible) css.display = 'none'
    }

    // Overflow logic
    const ox = getVal(settings, 'overflow_x', device)
    const oy = getVal(settings, 'overflow_y', device)
    if (ox && ox !== 'visible') css.overflowX = ox
    if (oy && oy !== 'visible') css.overflowY = oy

    return css
}

export function getVisibilityClasses(settings: ModuleSettings, device: string = 'desktop', isBuilder: boolean = false): string {
    const disabledOn = getVal(settings, 'disable_on', device)
    const isHidden = Array.isArray(disabledOn) && disabledOn.includes(device)

    if (isHidden) {
        return isBuilder ? 'opacity-30 pointer-events-auto' : 'hidden'
    }

    return ''
}

export function getAnimationClasses(settings: ModuleSettings, device: string = 'desktop'): string {
    const nested = getResponsiveValue(settings, 'animation', device)
    const effect = (nested && nested.effect) || getResponsiveValue(settings, 'animation_effect', device)
    return effect || ''
}

export function getPositioningStyles(settings: ModuleSettings, device: string = 'desktop'): Record<string, any> {
    const styles: Record<string, any> = {}
    const zIndex = getVal(settings, 'z_index', device) || getResponsiveValue(settings, 'zIndex', device)

    if (zIndex !== undefined && zIndex !== '' && zIndex !== 0) {
        styles.zIndex = zIndex
    }

    const pos = getVal(settings, 'position', device)
    if (pos && pos !== 'static' && pos !== 'default') {
        styles.position = pos
        const top = getVal(settings, 'top', device); if (top) styles.top = toCSS(top)
        const bottom = getVal(settings, 'bottom', device); if (bottom) styles.bottom = toCSS(bottom)
        const left = getVal(settings, 'left', device); if (left) styles.left = toCSS(left)
        const right = getVal(settings, 'right', device); if (right) styles.right = toCSS(right)
    }

    return styles
}

export function getTransitionStyles(settings: ModuleSettings, device: string = 'desktop'): Record<string, any> {
    const css: Record<string, any> = {}
    const duration = getVal(settings, 'transition_duration', device)
    const delay = getVal(settings, 'transition_delay', device)
    const curve = getVal(settings, 'transition_curve', device)

    if (duration !== undefined && (duration as number) > 0) {
        css.transitionDuration = `${duration}ms`
        css.transitionProperty = 'all' // Default to all
        if (delay) css.transitionDelay = `${delay}ms`
        if (curve && curve !== 'auto') css.transitionTimingFunction = curve
    }

    return css
}

export function getColorVariables(settings: ModuleSettings, hoverSettings: ModuleSettings = {}, device: string = 'desktop'): Record<string, string> {
    const styles: Record<string, string> = {}

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

/**
 * Premium Utility: Glassmorphism Styles
 */
export function getGlassStyles(settings: ModuleSettings, prefix: string = '', device: string = 'desktop'): Record<string, any> {
    const css: Record<string, any> = {}
    const p = prefix ? `${prefix}_` : ''

    if (getVal(settings, `${p}enable_glass`, device)) {
        const blur = getVal(settings, `${p}glass_blur`, device) || 10
        const opacity = getVal(settings, `${p}glass_opacity`, device) || 10
        const color = getVal(settings, `${p}glass_color`, device) || '#ffffff'
        const border = getVal(settings, `${p}glass_border`, device) || 1

        css.backdropFilter = `blur(${blur}px)`
        css.webkitBackdropFilter = `blur(${blur}px)`

        // Convert hex to rgba for background
        const { h, s, l } = hexToHsl(color)
        css.backgroundColor = `hsla(${h}, ${s}%, ${l}%, ${opacity / 100})`

        if (border > 0) {
            css.border = `${border}px solid hsla(${h}, ${s}%, ${l}%, 0.2)`
        }
    }

    return css
}

/**
 * Premium Utility: Text Gradient Styles
 */
export function getTextGradientStyles(settings: ModuleSettings, prefix: string = '', device: string = 'desktop'): Record<string, any> {
    const css: Record<string, any> = {}
    const p = prefix ? `${prefix}_` : ''

    if (getVal(settings, `${p}use_gradient`, device)) {
        const g = getVal(settings, `${p}gradient`, device)
        if (g && g.stops && g.stops.length >= 2) {
            css.backgroundImage = generateGradientCSS(g)
            css.webkitBackgroundClip = 'text'
            css.backgroundClip = 'text'
            css.color = 'transparent'
            css.display = 'inline-block' // Required for clip to work well
        }
    }

    return css
}

/**
 * Premium Utility: Mask Styles
 */
export function getMaskStyles(settings: ModuleSettings, prefix: string = '', device: string = 'desktop'): Record<string, any> {
    const css: Record<string, any> = {}
    const p = prefix ? `${prefix}_` : ''
    const shape = getVal(settings, `${p}mask_shape`, device)

    if (shape && shape !== 'none') {
        const masks: Record<string, string> = {
            circle: 'circle(50% at 50% 50%)',
            squircle: 'polygon(33% 0%, 67% 0%, 100% 33%, 100% 67%, 67% 100%, 33% 100%, 0% 67%, 0% 33%)',
            diamond: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)',
            triangle: 'polygon(50% 0%, 0% 100%, 100% 100%)',
            blob1: 'path("M48.5,-57.7C61.4,-48.4,69.5,-31.2,71.2,-13.6C72.9,4,68.2,22,57.7,35.5C47.2,49,30.9,58,13.7,62.1C-3.5,66.1,-21.5,65.2,-36.8,57.1C-52,49,-64.5,33.7,-68.6,16.5C-72.7,-0.7,-68.4,-19.8,-57.9,-32.1C-47.3,-44.4,-30.5,-49.9,-15.1,-57.4C0.4,-64.9,24.3,-71.1,48.5,-57.7Z")',
        }

        if (masks[shape]) {
            css.clipPath = masks[shape]
            css.webkitClipPath = masks[shape]
        }
    }

    return css
}
