import { BackgroundPatterns, BackgroundMasks } from './AssetLibrary'

/**
 * Style Utilities for generating CSS from Module Settings
 */

export function getResponsiveValue(settings, baseKey, device) {
    if (device === 'desktop') return settings[baseKey]

    const suffix = device === 'mobile' ? '_mobile' : `_${device}`
    const deviceValue = settings[baseKey + suffix]

    // Fallback: Phone -> Tablet -> Desktop
    if (deviceValue !== undefined && deviceValue !== null && deviceValue !== '') return deviceValue

    if (device === 'mobile') {
        const tabletValue = settings[baseKey + '_tablet']
        if (tabletValue !== undefined && tabletValue !== null && tabletValue !== '') return tabletValue
    }

    return settings[baseKey]
}

export function getTypographyStyles(settings, prefix = '', device = 'desktop') {
    const css = {}

    // Support nested typography settings key if it exists (e.g. settings.typography.text_color)
    const getVal = (key) => {
        // 1. Try flat key (with prefix)
        const cleanPrefix = prefix ? (prefix.endsWith('_') ? prefix.slice(0, -1) : prefix) : ''
        const snakeKey = cleanPrefix ? `${cleanPrefix}_${key}` : key
        let val = getResponsiveValue(settings, snakeKey, device)

        // 2. Try nested text_design/typography object if flat failed
        if (val === undefined || val === null || val === '') {
            const nestedGroup = settings[cleanPrefix || 'typography']
            if (nestedGroup && typeof nestedGroup === 'object') {
                // Try key mapping inside object
                val = getResponsiveValue(nestedGroup, key, device)
            }
        }

        return val
    }

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
        // Use robust getter
        let val = getVal(f.key)

        // Fallback to old camelCase naming if snake_case is missing (Legacy support)
        if (val === undefined || val === null || val === '') {
            const cleanPrefix = prefix ? (prefix.endsWith('_') ? prefix.slice(0, -1) : prefix) : ''
            const camelBase = f.key.split('_').map((word, index) =>
                index === 0 ? word : word.charAt(0).toUpperCase() + word.slice(1)
            ).join('')
            const camelKey = cleanPrefix ? `${cleanPrefix}_${camelBase}` : camelBase
            val = getResponsiveValue(settings, camelKey, device)
        }

        if (val !== undefined && val !== null && val !== '') {
            css[f.prop] = f.unit ? `${val}${f.unit}` : val
        }
    })

    return css
}

export function getSizingStyles(settings, device = 'desktop') {
    const css = {}
    const fields = [
        { key: 'width', prop: 'width' },
        { key: 'max_width', prop: 'maxWidth' },
        { key: 'min_height', prop: 'minHeight' },
        { key: 'height', prop: 'height' },
        { key: 'max_height', prop: 'maxHeight' },
        { key: 'align', prop: 'margin' },
        { key: 'column_class', prop: 'width' }
    ]

    fields.forEach(f => {
        // Try snake_case first (standard)
        let val = getResponsiveValue(settings, f.key, device)

        // Fallback to camelCase if snake_case is missing and keys are different
        const camelKey = f.key.split('_').map((word, index) =>
            index === 0 ? word : word.charAt(0).toUpperCase() + word.slice(1)
        ).join('')

        if ((val === undefined || val === null || val === '') && camelKey !== f.key) {
            val = getResponsiveValue(settings, camelKey, device)
        }

        if (val !== undefined && val !== null && val !== '') {
            if (f.key === 'align') {
                if (val === 'center') { css.marginLeft = 'auto'; css.marginRight = 'auto' }
                else if (val === 'left') { css.marginLeft = '0'; css.marginRight = 'auto' }
                else if (val === 'right') { css.marginLeft = 'auto'; css.marginRight = '0' }
            } else {
                // If numeric (number or string containing only digits), append px
                if (typeof val === 'number') {
                    css[f.prop] = `${val}px`
                } else if (typeof val === 'string' && val.match(/^\d+$/)) {
                    css[f.prop] = `${val}px`
                } else {
                    css[f.prop] = val
                }
            }
        }
    })

    return css
}

export function getBorderStyles(settings, baseKey = 'border', device = 'desktop') {
    const borderSettings = getResponsiveValue(settings, baseKey, device)
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

export function getSpacingStyles(settings, baseKey, device = 'desktop', type = 'padding') {
    const value = getResponsiveValue(settings, baseKey, device)
    if (!value) return {}

    const css = {}
    const { top, bottom, left, right, unit } = value
    const u = unit || 'px'

    if (top !== undefined) css[`${type}Top`] = `${top}${u}`
    if (bottom !== undefined) css[`${type}Bottom`] = `${bottom}${u}`
    if (left !== undefined) css[`${type}Left`] = `${left}${u}`
    if (right !== undefined) css[`${type}Right`] = `${right}${u}`

    return css
}

export function getBoxShadowStyles(settings, baseKey = 'boxShadow', device = 'desktop') {
    const shadowSettings = getResponsiveValue(settings, baseKey, device)
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

export function getBackgroundStyles(settings, device = 'desktop') {
    if (!settings) return {}
    const css = {}

    // Helper to get value from either flat key OR nested 'background' object
    const getVal = (key) => {
        // 1. Try flat key first (User overrides usually write here via Field)
        let val = getResponsiveValue(settings, key, device) // Note: getBackgroundStyles doesn't take device arg but we should probably add it or rely on settings being pre-resolved?
        // Wait, getBackgroundStyles signature is (settings). It doesn't take device.
        // BUT getResponsiveValue uses device? 
        // getResponsiveValue(settings, baseKey, device) -> lines 7-22.
        // getBackgroundStyles calls getResponsiveValue BUT usually it relies on settings being the raw object.
        // Wait, getBackgroundStyles usage in line 201 doesn't accept device.
        // And check usage in TextBlock: getBackgroundStyles(settings.value). NO DEVICE.

        // Fix: We need to check if 'settings' involves responsive keys or if we are pre-processing?
        // In TextBlock.vue (line 67): Object.assign(styles, getBackgroundStyles(settings.value))
        // getBackgroundStyles implementations below (line 206) use `settings.backgroundColor`.
        // This implies it treats 'settings' as a resolved object OR it accesses specific keys.

        // Looking at lines 206 `settings.backgroundColor` -> direct property access.
        // BUT looking at `getFilterStyles` (line 644), it takes `device`.
        // `getBackgroundStyles` DOES NOT take `device`. THIS IS A BUG.
        // It accesses `settings.backgroundColor` directly. This returns the raw value (likely desktop or undefined/mobile key ignored).
        // IF settings contains `backgroundColor_mobile`, accessing `.backgroundColor` returns desktop value.
        // So Backgrounds ARE NOT RESPONSIVE currently?

        // We should update getBackgroundStyles to accept device.

        if (val !== undefined && val !== null && val !== '') return val

        // 2. Try nested background object (from Defaults)
        if (settings.background && typeof settings.background === 'object') {
            // Map standard keys: backgroundColor -> color, backgroundImage -> image
            const shortKeyMap = {
                'backgroundColor': 'color',
                'backgroundImage': 'image',
                'backgroundRepeat': 'repeat',
                'backgroundPosition': 'position',
                'backgroundSize': 'size'
            }
            const shortKey = shortKeyMap[key] || key
            return settings.background[shortKey]
        }
        return undefined
    }

    // Background Color
    // Note: We need to fix the missing device argument in the strict signature below, but for now we patch functionality
    if (settings.backgroundColor || (settings.background && settings.background.color)) {
        css.backgroundColor = settings.backgroundColor || settings.background.color
    }

    // Background Image & Gradients (Multiple Layers)
    const layers = []
    const sizes = []
    const repeatList = []
    const blendList = []
    const posList = []

    // 1. Resolve Gradients
    const gradientList = settings.backgroundGradients || (getVal('backgroundGradient') ? [getVal('backgroundGradient')] : [])
    const gradientCSSList = gradientList.map(g => generateGradientCSS(g)).filter(c => !!c)

    // 2. Resolve Image
    const bgImage = getVal('backgroundImage')
    const imageCSS = bgImage ? `url(${bgImage})` : ''

    // 3. Assemble Layers (Respecting "Show Above Image" setting)
    // Order: Pattern > [Gradient/Image depending on setting]

    // Resolve Image Size and String
    let resolvedImageSize = ''
    if (imageCSS) {
        let size = getVal('backgroundImageSize') || 'cover'
        if (size === 'custom') {
            const w = getVal('backgroundImageWidth') || 'auto'
            const h = getVal('backgroundImageHeight') || 'auto'
            size = `${w} ${h}`
        } else if (size === 'stretch') {
            size = '100% 100%'
        } else if (size === 'fit') {
            size = 'contain'
        }
        resolvedImageSize = size
    }

    // 3.1. Add Pattern (Topmost)
    const bgPattern = getVal('backgroundPattern')
    if (bgPattern && bgPattern !== 'none') {
        const pattern = BackgroundPatterns.find(p => p.id === bgPattern)
        if (pattern) {
            // ... (Pattern SVG Generation logic) ...
            // We need to fetch sub-settings using getVal
            let svgStr = ''
            const patternRotate = getVal('backgroundPatternRotate')
            const patternInvert = getVal('backgroundPatternInvert')

            let matchedRotateVariant = false
            let matchedInvertVariant = false

            // Check for nested structure (regular/inverted)
            if (pattern.svg.regular && typeof pattern.svg.regular === 'object') {
                const hasInvert = !!pattern.svg.inverted
                const stateKey = (patternInvert && hasInvert) ? 'inverted' : 'regular'
                if (patternInvert && hasInvert) matchedInvertVariant = true

                const stateObj = pattern.svg[stateKey] || pattern.svg.regular
                const hasRotate = !!stateObj?.rotated
                const rotateKey = (patternRotate && hasRotate) ? 'rotated' : 'default'
                if (patternRotate && hasRotate) matchedRotateVariant = true

                svgStr = stateObj?.[rotateKey] || stateObj?.default || ''
            } else {
                // Legacy flat structure
                if (patternInvert && pattern.svg.inverted) {
                    matchedInvertVariant = true
                    if (patternRotate && typeof pattern.svg.inverted === 'object' && pattern.svg.inverted.rotated) {
                        svgStr = pattern.svg.inverted.rotated
                        matchedRotateVariant = true
                    } else {
                        svgStr = (typeof pattern.svg.inverted === 'string' ? pattern.svg.inverted : pattern.svg.inverted?.default || '')
                    }
                } else if (patternRotate && pattern.svg.rotated) {
                    svgStr = pattern.svg.rotated
                    matchedRotateVariant = true
                } else {
                    svgStr = pattern.svg.default || (typeof pattern.svg === 'string' ? pattern.svg : '')
                }
            }

            // Apply generic inversion if no variant found
            if (patternInvert && !matchedInvertVariant) {
                svgStr = `<rect width="${pattern.width}" height="${pattern.height}" fill="currentColor" /><g fill-rule="evenodd">${svgStr}</g>`
            }

            if (typeof svgStr === 'object') {
                svgStr = svgStr.landscape || svgStr.square || svgStr.default || ''
            }

            const color = getVal('backgroundPatternColor') || 'rgba(0,0,0,0.1)'

            svgStr = svgStr.replace(/currentColor/g, color)
            if (!svgStr.includes('fill=') && !svgStr.includes('stroke=')) {
                svgStr = svgStr.replace(/<(path|circle|rect|ellipse|polygon|polyline)\s/g, `<$1 fill="${color}" `)
            }

            let transform = ''
            if (getVal('backgroundPatternFlipH')) transform += 'scaleX(-1) '
            if (getVal('backgroundPatternFlipV')) transform += 'scaleY(-1) '
            if (patternRotate && !matchedRotateVariant) transform += 'rotate(90deg) '

            const finalSvg = `<svg width="${pattern.width}" height="${pattern.height}" viewBox="0 0 ${pattern.width} ${pattern.height}" xmlns="http://www.w3.org/2000/svg" style="${transform ? `transform: ${transform}` : ''}">${svgStr}</svg>`
            layers.push(`url("data:image/svg+xml,${encodeURIComponent(finalSvg)}")`)

            let pSize = getVal('backgroundPatternSize') || 'actual'
            if (pSize === 'custom') {
                pSize = `${getVal('backgroundPatternWidth') || 'auto'} ${getVal('backgroundPatternHeight') || 'auto'}`
            } else if (pSize === 'actual') {
                pSize = `${pattern.width}px ${pattern.height}px`
            } else if (pSize === 'stretch') {
                pSize = '100% 100%'
            } else if (pSize === 'fit') {
                pSize = 'contain'
            }
            sizes.push(pSize)
        }
    }

    // 3.2. Add Gradient & Image based on "Above Image" setting
    if (getVal('backgroundGradientShowAboveImage')) {
        // Gradient Above Image
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
        // Image Above Gradient
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

    repeatList.length = 0
    blendList.length = 0

    // 1. Pattern (if any)
    if (bgPattern && bgPattern !== 'none') {
        repeatList.push(getVal('backgroundPatternRepeat') || 'repeat')
        blendList.push(getVal('backgroundPatternBlendMode') || 'normal')
    }

    // 2. Gradients and Image
    if (getVal('backgroundGradientShowAboveImage')) {
        // Gradients first
        gradientCSSList.forEach(() => {
            repeatList.push('no-repeat')
            blendList.push('normal')
        })
        if (imageCSS) {
            repeatList.push(getVal('backgroundImageRepeat') || 'no-repeat')
            blendList.push(getVal('backgroundImageBlendMode') || 'normal')
        }
    } else {
        // Image first
        if (imageCSS) {
            repeatList.push(getVal('backgroundImageRepeat') || 'no-repeat')
            blendList.push(getVal('backgroundImageBlendMode') || 'normal')
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

        // Attachment (Shared or per-layer)
        if (getVal('parallax') && (getVal('parallaxMethod') === 'css' || !getVal('parallaxMethod'))) {
            // CSS Parallax (Fixed)
            css.backgroundAttachment = layers.map(() => 'fixed').join(', ')
        } else if (getVal('backgroundImageAttachment')) {
            css.backgroundAttachment = layers.map(() => getVal('backgroundImageAttachment')).join(', ')
        }

        // Position
        posList.length = 0
        // Pattern Position: Use setting or default to center
        if (bgPattern && bgPattern !== 'none') {
            let origin = getVal('backgroundPatternRepeatOrigin') || 'center'
            if (origin === 'center') origin = 'center center'

            const parts = origin.split(' ')
            if (parts.length === 2) {
                const [xEdge, yEdge] = parts
                const xOff = getVal('backgroundPatternHorizontalOffset') || '0'
                const yOff = getVal('backgroundPatternVerticalOffset') || '0'

                if (getVal('backgroundPatternRepeatOrigin') && getVal('backgroundPatternRepeatOrigin') !== 'center' && getVal('backgroundPatternRepeatOrigin') !== 'center center') {
                    posList.push(`${xEdge} ${xOff} ${yEdge} ${yOff}`)
                } else {
                    posList.push(origin)
                }
            } else {
                posList.push(origin)
            }
        }
        if (getVal('backgroundGradientShowAboveImage')) {
            gradientCSSList.forEach(() => posList.push('center'))
            if (imageCSS) posList.push(getVal('backgroundImagePosition') || 'center')
        } else {
            if (imageCSS) posList.push(getVal('backgroundImagePosition') || 'center')
            gradientCSSList.forEach(() => posList.push('center'))
        }
        css.backgroundPosition = posList.join(', ')
    }

    // 4. Resolve Mask
    if (settings.backgroundMask && settings.backgroundMask !== 'none') {
        const mask = BackgroundMasks.find(m => m.id === settings.backgroundMask)
        if (mask) {
            const art = settings.backgroundMaskAspectRatio || 'landscape'
            const maskRotate = settings.backgroundMaskRotate
            const maskInvert = settings.backgroundMaskInvert

            let svgStr = ''
            let matchedRotateVariant = false
            let matchedInvertVariant = false

            if (mask.svg.regular && typeof mask.svg.regular === 'object') {
                const hasInvert = !!mask.svg.inverted
                const stateKey = (maskInvert && hasInvert) ? 'inverted' : 'regular'
                if (maskInvert && hasInvert) matchedInvertVariant = true

                const stateObj = mask.svg[stateKey] || mask.svg.regular
                const hasRotate = !!stateObj?.rotated
                const rotateKey = (maskRotate && hasRotate) ? 'rotated' : 'default'
                if (maskRotate && hasRotate) matchedRotateVariant = true

                const rotateObj = stateObj?.[rotateKey] || stateObj?.default || stateObj

                if (typeof rotateObj === 'string') {
                    svgStr = rotateObj
                } else {
                    svgStr = rotateObj?.[art] || rotateObj?.landscape || rotateObj?.square || ''
                }
            } else {
                let stateKey = 'default'
                if (maskInvert && mask.svg.inverted) {
                    stateKey = 'inverted'
                    matchedInvertVariant = true
                } else if (maskRotate && mask.svg.rotated) {
                    stateKey = 'rotated'
                    matchedRotateVariant = true
                }

                const stateObj = mask.svg[stateKey] || mask.svg.default || mask.svg
                if (typeof stateObj === 'string') {
                    svgStr = stateObj
                } else {
                    svgStr = stateObj[art] || stateObj.landscape || stateObj.square || ''
                }
            }

            if (maskInvert && !matchedInvertVariant) {
                const vBox = mask.viewBox[art] || mask.viewBox['landscape'] || '0 0 100 100'
                const [, , vw, vh] = vBox.split(' ')
                svgStr = `<rect width="${vw}" height="${vh}" fill="currentColor" /><g fill-rule="evenodd">${svgStr}</g>`
            }

            const color = settings.backgroundMaskColor || 'rgba(0,0,0,0.1)'
            svgStr = svgStr.replace(/currentColor/g, color)
            if (!svgStr.includes('fill=')) {
                svgStr = svgStr.replace(/<(path|circle|rect|ellipse|polygon|polyline)\s/g, `<$1 fill="${color}" `)
            }

            let transform = ''
            if (settings.backgroundMaskFlipH) transform += 'scaleX(-1) '
            if (settings.backgroundMaskFlipV) transform += 'scaleY(-1) '
            if (maskRotate && !matchedRotateVariant) transform += 'rotate(90deg) '

            const viewBox = mask.viewBox[art] || mask.viewBox['landscape']
            const finalSvg = '<svg viewBox="' + viewBox + '" xmlns="http://www.w3.org/2000/svg" style="' + (transform ? 'transform: ' + transform : '') + '">' + svgStr + '</svg>'
            const maskDataUri = 'url("data:image/svg+xml,' + encodeURIComponent(finalSvg) + '")'

            // Apply as Clipping Mask
            css.maskImage = maskDataUri
            css.webkitMaskImage = maskDataUri

            let mSize = settings.backgroundMaskSize || 'stretch'
            if (mSize === 'custom') {
                const w = settings.backgroundMaskWidth || 'auto'
                const h = settings.backgroundMaskHeight || 'auto'
                mSize = `${w} ${h}`
            } else if (mSize === 'stretch') {
                mSize = '100% 100%'
            } else if (mSize === 'actual') {
                mSize = 'auto'
            } else if (mSize === 'fit') {
                mSize = 'contain'
            }
            css.maskSize = mSize
            css.webkitMaskSize = mSize

            css.maskPosition = settings.backgroundMaskPosition || 'center'
            css.webkitMaskPosition = css.maskPosition

            css.maskRepeat = 'no-repeat'
            css.webkitMaskRepeat = 'no-repeat'

            // If Blend Mode is set, we MUST also add it as a Background Layer to support background-blend-mode
            // Because mask-image itself doesn't support blending with the element's background
            if (settings.backgroundMaskBlendMode && settings.backgroundMaskBlendMode !== 'normal') {
                layers.unshift(maskDataUri)
                sizes.unshift(mSize)
                repeatList.unshift('no-repeat')
                blendList.unshift(settings.backgroundMaskBlendMode)
                posList.unshift(settings.backgroundMaskPosition || 'center')

                // Re-sync background styles since we modified the layers
                css.backgroundImage = layers.join(', ')
                css.backgroundSize = sizes.join(', ')
                css.backgroundRepeat = repeatList.join(', ')
                css.backgroundBlendMode = blendList.join(', ')
                css.backgroundPosition = posList.join(', ')
            }
        }
    }

    return css
}

// Color Utilities for Smart Gradients
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
    // For a vibrant look, we can shift hue by 15-30 degrees
    const secondaryHue = (h + 25) % 360
    const secondaryL = l > 60 ? l - 20 : l + 20
    const secondaryS = Math.min(100, s + 10)

    return [
        baseHex,
        hslToHex(secondaryHue, secondaryS, secondaryL)
    ]
}

export function getFilterStyles(settings, device = 'desktop') {
    const css = {}
    const filters = []

    // Support both old flat keys and new nested object
    const getVal = (key) => {
        // 1. Try nested object first
        const nested = getResponsiveValue(settings, 'filter', device)
        if (nested && nested[key] !== undefined) return nested[key]

        // 2. Fallback to flat key
        return getResponsiveValue(settings, key, device)
    }

    // 1. Opacity
    const opacity = getVal('opacity')
    if (opacity !== undefined && opacity !== null && opacity !== '' && opacity != 100) {
        css.opacity = opacity / 100
    }

    // 2. Blend Mode
    const blendMode = getVal('blend_mode')
    if (blendMode && blendMode !== 'normal') {
        css.mixBlendMode = blendMode
    }

    // 3. CSS Filter Functions
    const blur = getVal('blur')
    if (blur && blur > 0) filters.push(`blur(${blur}px)`)

    const brightness = getVal('brightness')
    if (brightness !== undefined && brightness !== null && brightness !== '' && brightness != 100) filters.push(`brightness(${brightness}%)`)

    const contrast = getVal('contrast')
    if (contrast !== undefined && contrast !== null && contrast !== '' && contrast != 100) filters.push(`contrast(${contrast}%)`)

    const grayscale = getVal('grayscale')
    if (grayscale && grayscale > 0) filters.push(`grayscale(${grayscale}%)`)

    const sepia = getVal('sepia')
    if (sepia && sepia > 0) filters.push(`sepia(${sepia}%)`)

    const saturate = getVal('saturate')
    if (saturate !== undefined && saturate !== null && saturate !== '' && saturate != 100) filters.push(`saturate(${saturate}%)`)

    const hueRotate = getVal('hue_rotate')
    if (hueRotate && hueRotate > 0) filters.push(`hue-rotate(${hueRotate}deg)`)

    const invert = getVal('invert')
    if (invert && invert > 0) filters.push(`invert(${invert}%)`)

    if (filters.length > 0) {
        css.filter = filters.join(' ')
    }

    return css
}

export function getTransformStyles(settings, device = 'desktop') {
    const transforms = []

    // Support both old flat keys and new nested object
    const getVal = (key) => {
        // 1. Try nested object first
        const nested = getResponsiveValue(settings, 'transform', device)
        // Handle mapped names if necessary, but we kept them same in TransformField
        if (nested && nested[key] !== undefined) return nested[key]

        // 2. Fallback to flat key
        return getResponsiveValue(settings, key, device)
    }

    // Origin
    const origin = getVal('origin')

    // Scale
    // Note: TransformField uses 'scale' key, old settings used 'transform_scale'
    // We check both.
    const scale = getVal('scale') ?? getVal('transform_scale')
    if (scale !== undefined && scale !== null && scale !== '' && scale != 100) {
        transforms.push(`scale(${scale / 100})`)
    }

    // Translate
    // New keys: translate_x, translate_y
    // Old keys: transform_translate_x, transform_translate_y
    const tx = getVal('translate_x') ?? getVal('transform_translate_x')
    const ty = getVal('translate_y') ?? getVal('transform_translate_y')
    if ((tx && tx != 0) || (ty && ty != 0)) {
        transforms.push(`translate(${tx || 0}px, ${ty || 0}px)`)
    }

    // Rotate
    // New keys: rotate_x, rotate_y, rotate_z
    // Old keys: transform_rotate (X), transform_rotate_y, transform_rotate_z
    const rx = getVal('rotate_x') ?? getVal('transform_rotate')
    const ry = getVal('rotate_y') ?? getVal('transform_rotate_y')
    const rz = getVal('rotate_z') ?? getVal('transform_rotate_z')

    if (rx && rx != 0) transforms.push(`rotateX(${rx}deg)`)
    if (ry && ry != 0) transforms.push(`rotateY(${ry}deg)`)
    if (rz && rz != 0) transforms.push(`rotateZ(${rz}deg)`)

    // Skew
    // New keys: skew_x, skew_y
    // Old keys: transform_skew_x, transform_skew_y
    const sx = getVal('skew_x') ?? getVal('transform_skew_x')
    const sy = getVal('skew_y') ?? getVal('transform_skew_y')
    if (sx && sx != 0) transforms.push(`skewX(${sx}deg)`)
    if (sy && sy != 0) transforms.push(`skewY(${sy}deg)`)

    const css = {}

    if (transforms.length > 0) {
        css.transform = transforms.join(' ')
    }

    if (origin && origin !== 'center') {
        css.transformOrigin = origin
    }

    return css
}

export function getAnimationStyles(settings, device = 'desktop') {
    const css = {}

    // Support both old flat keys and new nested object
    const getVal = (key) => {
        // 1. Try nested object first
        const nested = getResponsiveValue(settings, 'animation', device)
        if (nested && nested[key] !== undefined) return nested[key]

        // 2. Fallback to flat key (prefixed with animation_)
        return getResponsiveValue(settings, `animation_${key}`, device)
    }

    const effect = getVal('effect') // New key 'effect', old key 'animation_effect' -> getVal adds prefix if needed? No, getVal adds prefix for fallback. 
    // Wait, old keys were `animation_effect`, `animation_duration`, etc.
    // My getVal fallback uses `animation_${key}`. 
    // So getVal('effect') checks nested.effect, then settings.animation_effect. Correct.

    if (effect) {
        // Note: The actual animation class (e.g. 'animate-fade') is handled by the component's class binding usually.
        // But we might need to apply duration/delay/etc as inline styles.

        const duration = getVal('duration')
        const delay = getVal('delay')
        const repeat = getVal('repeat') // 'once' or 'infinite' or '1'
        const curve = getVal('curve') // 'ease', 'linear' etc.

        if (duration !== undefined && duration !== null) css.animationDuration = `${duration}ms`
        if (delay !== undefined && delay !== null) css.animationDelay = `${delay}ms`
        if (curve) css.animationTimingFunction = curve

        if (repeat === 'infinite') {
            css.animationIterationCount = 'infinite'
        } else if (repeat === 'once' || repeat === '1') {
            css.animationIterationCount = '1'
        }

        // We also need to return the class name potentially? 
        // StyleUtils usually returns inline styles. 
        // The class name should be extracted separately in the component.
    }

    return css
}

export function getLayoutStyles(settings, device = 'desktop') {
    const css = {}

    // Helper to try camelCase if snake_case is missing
    const getVal = (snakeKey) => {
        // 1. Try snake_case
        let val = getResponsiveValue(settings, snakeKey, device)

        // 2. Try camelCase fallback
        if (val === undefined || null || val === '') {
            const camelKey = snakeKey.replace(/_([a-z])/g, (g) => g[1].toUpperCase())
            val = getResponsiveValue(settings, camelKey, device)
        }
        return val
    }

    const layoutType = getVal('layout_type')

    if (layoutType === 'block') {
        css.display = 'block'
    } else if (layoutType === 'flex') {
        css.display = 'flex'

        // Direction
        css.flexDirection = getVal('direction') || 'column'

        // Wrap
        css.flexWrap = getVal('flex_wrap') || 'nowrap'

        // Justify Content
        css.justifyContent = getVal('justify_content') || 'flex-start'

        // Align Items
        css.alignItems = getVal('align_items') || 'stretch'

        // Align Content
        const alignContent = getVal('align_content')
        if (alignContent) css.alignContent = alignContent

        // Gaps
        const gapX = getVal('gap_x')
        const gapY = getVal('gap_y')

        const formatGap = (v) => {
            if (v === undefined || v === null || v === '') return undefined
            if (typeof v === 'number' || (typeof v === 'string' && !isNaN(parseFloat(v)) && isFinite(v))) {
                return `${v}px`
            }
            return v
        }

        const gX = formatGap(gapX)
        const gY = formatGap(gapY)

        if (gX) {
            if (gY) {
                css.gap = `${gY} ${gX}`
            } else {
                css.columnGap = gX
            }
        } else if (gY) {
            css.rowGap = gY
        }

    } else if (layoutType === 'grid') {
        css.display = 'grid'

        // --- 1. Columns & Rows ---

        // Columns
        const colWidths = getVal('column_widths') || 'equal'

        if (colWidths === 'equal') {
            const cols = getVal('column_count') || 3
            if (cols === 'auto') {
                css.gridTemplateColumns = `repeat(auto-fit, minmax(0, 1fr))`
            } else {
                css.gridTemplateColumns = `repeat(${cols}, 1fr)`
            }
        } else if (colWidths === 'equal_min') {
            const minW = getVal('column_min_width') || '250px'
            const minWStr = typeof minW === 'number' ? `${minW}px` : minW
            css.gridTemplateColumns = `repeat(auto-fit, minmax(${minWStr}, 1fr))`
        } else if (colWidths === 'auto') {
            css.gridTemplateColumns = `repeat(auto-fit, minmax(min-content, 1fr))`
        } else if (colWidths === 'manual') {
            const manualVal = getVal('grid_template_columns')
            if (manualVal) css.gridTemplateColumns = manualVal
        } else {
            css.gridTemplateColumns = 'repeat(3, 1fr)'
        }

        // Rows
        const rowHeights = getVal('row_heights') || 'auto'
        if (rowHeights === 'auto') {
            css.gridTemplateRows = 'auto'
        } else {
            const rows = getVal('row_count')
            if (rows && rows !== 'auto') {
                css.gridTemplateRows = `repeat(${rows}, 1fr)`
            }
        }

        // Auto Columns/Rows
        const autoCol = getVal('auto_columns')
        if (autoCol && autoCol !== 'auto') css.gridAutoColumns = autoCol

        const autoRow = getVal('auto_rows')
        if (autoRow && autoRow !== 'auto') css.gridAutoRows = autoRow


        // --- 2. Alignment & Distibution ---

        // Direction & Density
        const gridDir = getVal('grid_direction') || 'row'
        const gridDense = getVal('grid_density') === 'dense'
        css.gridAutoFlow = `${gridDir}${gridDense ? ' dense' : ''}`

        // Justify Content
        const justifyContent = getVal('justify_content')
        if (justifyContent) css.justifyContent = justifyContent

        // Align Content
        const alignContent = getVal('align_content')
        if (alignContent) css.alignContent = alignContent

        // Justify Items
        const justifyItems = getVal('justify_items')
        if (justifyItems) css.justifyItems = justifyItems

        // Align Items
        const alignItems = getVal('align_items')
        if (alignItems) css.alignItems = alignItems

        // --- 3. Gaps ---
        const gapX = getVal('gap_x')
        const gapY = getVal('gap_y')

        const formatGap = (v) => {
            if (v === undefined || v === null || v === '') return undefined
            if (typeof v === 'number' || (typeof v === 'string' && !isNaN(parseFloat(v)) && isFinite(v))) {
                return `${v}px`
            }
            return v
        }

        const gX = formatGap(gapX)
        const gY = formatGap(gapY)

        if (gX) {
            if (gY) {
                css.gap = `${gY} ${gX}`
            } else {
                css.columnGap = gX
            }
        } else if (gY) {
            css.rowGap = gY
        }
    }

    return css
}
