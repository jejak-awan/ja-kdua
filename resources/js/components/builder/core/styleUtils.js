import { BackgroundPatterns, BackgroundMasks } from './AssetLibrary'

/**
 * Style Utilities for generating CSS from Module Settings
 */

export function getResponsiveValue(settings, baseKey, device) {
    if (device === 'desktop') return settings[baseKey]

    const suffix = device === 'mobile' ? '_phone' : `_${device}`
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
    const fields = [
        { key: 'fontSize', prop: 'fontSize', unit: 'px' },
        { key: 'lineHeight', prop: 'lineHeight' },
        { key: 'letterSpacing', prop: 'letterSpacing', unit: 'px' },
        { key: 'textColor', prop: 'color' },
        { key: 'fontWeight', prop: 'fontWeight' },
        { key: 'fontStyle', prop: 'fontStyle' },
        { key: 'textAlignment', prop: 'textAlign' },
        { key: 'textTransform', prop: 'textTransform' },
        { key: 'textDecoration', prop: 'textDecoration' }
    ]

    fields.forEach(f => {
        const fullKey = prefix ? `${prefix}_${f.key}` : f.key
        const val = getResponsiveValue(settings, fullKey, device)
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
        { key: 'maxWidth', prop: 'maxWidth' },
        { key: 'minHeight', prop: 'minHeight' },
        { key: 'height', prop: 'height' },
        { key: 'maxHeight', prop: 'maxHeight' },
        { key: 'align', prop: 'margin' },
        { key: 'column_class', prop: 'width' }
    ]

    fields.forEach(f => {
        const val = getResponsiveValue(settings, f.key, device)
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
    const repeatList = []
    const blendList = []
    const posList = []

    // 1. Resolve Gradients
    const gradientList = settings.backgroundGradients || (settings.backgroundGradient ? [settings.backgroundGradient] : [])
    const gradientCSSList = gradientList.map(g => generateGradientCSS(g)).filter(c => !!c)

    // 2. Resolve Image
    const imageCSS = settings.backgroundImage ? `url(${settings.backgroundImage})` : ''

    // 3. Assemble Layers (Respecting "Show Above Image" setting)
    // Order: Pattern > [Gradient/Image depending on setting]

    // Resolve Image Size and String
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

    // 3.1. Add Pattern (Topmost)
    if (settings.backgroundPattern && settings.backgroundPattern !== 'none') {
        const pattern = BackgroundPatterns.find(p => p.id === settings.backgroundPattern)
        if (pattern) {
            // Resolve SVG based on rotate/invert state
            // Nested structure: svg.regular.default/rotated, svg.inverted.default/rotated
            // Legacy structure: svg.default, svg.rotated, svg.inverted
            let svgStr = ''
            const patternRotate = settings.backgroundPatternRotate
            const patternInvert = settings.backgroundPatternInvert

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
                // Wrap in a group with evenodd to "cut out" the pattern from a solid rect
                svgStr = `<rect width="${pattern.width}" height="${pattern.height}" fill="currentColor" /><g fill-rule="evenodd">${svgStr}</g>`
            }

            // Handle if svgStr is still an object (nested by aspect ratio)
            if (typeof svgStr === 'object') {
                svgStr = svgStr.landscape || svgStr.square || svgStr.default || ''
            }

            const color = settings.backgroundPatternColor || 'rgba(0,0,0,0.1)'

            // Modern processing: Replace ALL instances of currentColor
            // And inject fill="currentColor" to any path/circle/rect that lacks it for legacy support
            svgStr = svgStr.replace(/currentColor/g, color)

            // Robust injection: Only if it's not already using stroke or has a fill defined
            if (!svgStr.includes('fill=') && !svgStr.includes('stroke=')) {
                svgStr = svgStr.replace(/<(path|circle|rect|ellipse|polygon|polyline)\s/g, `<$1 fill="${color}" `)
            } else if (svgStr.includes('stroke=') && !svgStr.includes('stroke="none"')) {
                // If it has stroke but no fill, ensure stroke gets the color if it was using currentColor or was missing it
                // Note: replace(/currentColor/g, color) already handled explicit currentColor
            }

            let transform = ''
            if (settings.backgroundPatternFlipH) transform += 'scaleX(-1) '
            if (settings.backgroundPatternFlipV) transform += 'scaleY(-1) '
            if (patternRotate && !matchedRotateVariant) transform += 'rotate(90deg) '

            const finalSvg = `<svg width="${pattern.width}" height="${pattern.height}" viewBox="0 0 ${pattern.width} ${pattern.height}" xmlns="http://www.w3.org/2000/svg" style="${transform ? `transform: ${transform}` : ''}">${svgStr}</svg>`
            layers.push(`url("data:image/svg+xml,${encodeURIComponent(finalSvg)}")`)

            let pSize = settings.backgroundPatternSize || 'actual'
            if (pSize === 'custom') {
                pSize = `${settings.backgroundPatternWidth || 'auto'} ${settings.backgroundPatternHeight || 'auto'}`
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
    if (settings.backgroundGradientShowAboveImage) {
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
    if (settings.backgroundPattern && settings.backgroundPattern !== 'none') {
        repeatList.push(settings.backgroundPatternRepeat || 'repeat')
        blendList.push(settings.backgroundPatternBlendMode || 'normal')
    }

    // 2. Gradients and Image
    if (settings.backgroundGradientShowAboveImage) {
        // Gradients first
        gradientCSSList.forEach(() => {
            repeatList.push('no-repeat')
            blendList.push('normal') // Gradients don't usually have blend mode in this flat model
        })
        if (imageCSS) {
            repeatList.push(settings.backgroundImageRepeat || 'no-repeat')
            blendList.push(settings.backgroundImageBlendMode || 'normal')
        }
    } else {
        // Image first
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

        // Attachment (Shared or per-layer)
        if (settings.parallax && (settings.parallaxMethod === 'css' || !settings.parallaxMethod)) {
            // CSS Parallax (Fixed)
            css.backgroundAttachment = layers.map(() => 'fixed').join(', ')
        } else if (settings.backgroundImageAttachment) {
            css.backgroundAttachment = layers.map(() => settings.backgroundImageAttachment).join(', ')
        }

        // Position
        posList.length = 0
        // Pattern Position: Use setting or default to center
        if (settings.backgroundPattern && settings.backgroundPattern !== 'none') {
            let origin = settings.backgroundPatternRepeatOrigin || 'center'

            // If offsets are present and origin is NOT center (simple), try 4-value syntax
            // Values: 'left top', 'center top', 'right top', etc.
            // 4-value syntax: [x-edge] [x-offset] [y-edge] [y-offset]
            // We need to parse origin.

            if (origin === 'center') origin = 'center center'

            const parts = origin.split(' ')
            if (parts.length === 2) {
                const [xEdge, yEdge] = parts
                const xOff = settings.backgroundPatternHorizontalOffset || '0'
                const yOff = settings.backgroundPatternVerticalOffset || '0'

                // Only use 4-value syntax if we have offsets OR if we want to be explicit.
                // However, 'center 10px' invalid. 'center' must be used with edge keywords? 
                // Actually 'center' is compatible. 'left 10px top 20px' works.
                // 'center 10px' -> 'center' is x-pos? CSS says: "If one value is center, the other value is the offset" -> no.
                // 4-value syntax requires explicit edges. 'center' is valid edge keyword?
                // MDN: "background-position: bottom 10px right 20px;"
                // "center" is NOT a valid edge for 4-value syntax in some browsers / contexts if mixed?
                // Actually "center" *can* be used.
                // But let's stick to what the user provides.
                // If origin is "left top", use "left xOff top yOff".

                if (settings.backgroundPatternRepeatOrigin && settings.backgroundPatternRepeatOrigin !== 'center' && settings.backgroundPatternRepeatOrigin !== 'center center') {
                    posList.push(`${xEdge} ${xOff} ${yEdge} ${yOff}`)
                } else {
                    posList.push(origin)
                }
            } else {
                posList.push(origin)
            }
        }
        if (settings.backgroundGradientShowAboveImage) {
            gradientCSSList.forEach(() => posList.push('center'))
            if (imageCSS) posList.push(settings.backgroundImagePosition || 'center')
        } else {
            if (imageCSS) posList.push(settings.backgroundImagePosition || 'center')
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
    const layoutType = getResponsiveValue(settings, 'layout_type', device)

    if (layoutType === 'block') {
        css.display = 'block'
    } else if (layoutType === 'flex') {
        css.display = 'flex'

        // Direction
        css.flexDirection = getResponsiveValue(settings, 'direction', device) || 'column'

        // Wrap
        css.flexWrap = getResponsiveValue(settings, 'flex_wrap', device) || 'nowrap'

        // Justify Content
        css.justifyContent = getResponsiveValue(settings, 'justify_content', device) || 'flex-start'

        // Align Items
        css.alignItems = getResponsiveValue(settings, 'align_items', device) || 'stretch'

        // Align Content
        const alignContent = getResponsiveValue(settings, 'align_content', device)
        if (alignContent) css.alignContent = alignContent

        // Gaps
        const gapX = getResponsiveValue(settings, 'gap_x', device)
        const gapY = getResponsiveValue(settings, 'gap_y', device)

        const formatGap = (v) => {
            if (v === undefined || v === null || v === '') return undefined
            // If it's a number, or a numeric string without non-digit chars (except dot), append px
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
        const colWidths = getResponsiveValue(settings, 'column_widths', device) || 'equal'

        if (colWidths === 'equal') {
            // Standard: repeat(N, 1fr)
            const cols = getResponsiveValue(settings, 'column_count', device) || 3
            // Handle if cols is a number or string like 'auto'? 
            // If cols is 'auto', we can't really do repeat(auto, 1fr) validly in CSS grid without auto-fit/fill context
            // But if user sets 'auto' in column_count while in 'equal' mode, we might assume they want auto-fit?
            // Let's coerce:
            if (cols === 'auto') {
                css.gridTemplateColumns = `repeat(auto-fit, minmax(0, 1fr))`
            } else {
                css.gridTemplateColumns = `repeat(${cols}, 1fr)`
            }
        } else if (colWidths === 'equal_min') {
            // Responsive Auto-Fit: repeat(auto-fit, minmax(MIN_WIDTH, 1fr))
            const minW = getResponsiveValue(settings, 'column_min_width', device) || '250px'
            // Ensure unit? Dimension field usually provides unit.
            const minWStr = typeof minW === 'number' ? `${minW}px` : minW
            css.gridTemplateColumns = `repeat(auto-fit, minmax(${minWStr}, 1fr))`
        } else if (colWidths === 'auto') {
            // Auto Width: repeat(auto-fit, minmax(min-content, 1fr)) or similar
            // This distributes columns based on content size, but ensuring they fill space?
            // Or maybe Just auto? 
            // "Auto Width Columns" usually means: let content dictate width.
            // `repeat(auto-fit, minmax(auto, 1fr))`?
            css.gridTemplateColumns = `repeat(auto-fit, minmax(min-content, 1fr))`
        } else if (colWidths === 'manual') {
            // Manual: Direct use of value
            const manualVal = getResponsiveValue(settings, 'grid_template_columns', device)
            if (manualVal) css.gridTemplateColumns = manualVal
        } else {
            // Fallback
            css.gridTemplateColumns = 'repeat(3, 1fr)'
        }

        // Rows
        const rowHeights = getResponsiveValue(settings, 'row_heights', device) || 'auto'
        if (rowHeights === 'auto') {
            css.gridTemplateRows = 'auto' // Default
        } else {
            // If user selects custom, maybe they want explicit rows?
            // Since we don't have a complex repeater yet, let's look for row_count
            const rows = getResponsiveValue(settings, 'row_count', device)
            if (rows && rows !== 'auto') {
                css.gridTemplateRows = `repeat(${rows}, 1fr)`
            }
        }

        // Auto Columns/Rows (Implicit Grid)
        const autoCol = getResponsiveValue(settings, 'auto_columns', device)
        if (autoCol && autoCol !== 'auto') css.gridAutoColumns = autoCol

        const autoRow = getResponsiveValue(settings, 'auto_rows', device)
        if (autoRow && autoRow !== 'auto') css.gridAutoRows = autoRow


        // --- 2. Alignment & Distibution ---

        // Direction & Density (grid-auto-flow)
        const gridDir = getResponsiveValue(settings, 'grid_direction', device) || 'row'
        const gridDense = getResponsiveValue(settings, 'grid_density', device) === 'dense'
        css.gridAutoFlow = `${gridDir}${gridDense ? ' dense' : ''}`

        // Justify Content (distribution along row axis)
        const justifyContent = getResponsiveValue(settings, 'justify_content', device)
        if (justifyContent) css.justifyContent = justifyContent

        // Align Content (distribution along block axis)
        const alignContent = getResponsiveValue(settings, 'align_content', device)
        if (alignContent) css.alignContent = alignContent

        // Justify Items (alignment within cell - inline axis)
        const justifyItems = getResponsiveValue(settings, 'justify_items', device)
        if (justifyItems) css.justifyItems = justifyItems

        // Align Items (alignment within cell - block axis)
        const alignItems = getResponsiveValue(settings, 'align_items', device)
        if (alignItems) css.alignItems = alignItems

        // --- 3. Gaps ---
        const gapX = getResponsiveValue(settings, 'gap_x', device)
        const gapY = getResponsiveValue(settings, 'gap_y', device)

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
