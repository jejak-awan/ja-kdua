/**
 * Color Utility Functions for Builder
 */

// HEX to RGB
export function hexToRgb(hex) {
    // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
    const shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i
    hex = hex.replace(shorthandRegex, (m, r, g, b) => r + r + g + g + b + b)

    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null
}

// RGB to HEX
export function rgbToHex(r, g, b) {
    return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)
}

// RGB to HSV
export function rgbToHsv(r, g, b) {
    r /= 255, g /= 255, b /= 255
    const max = Math.max(r, g, b), min = Math.min(r, g, b)
    let h, s, v = max

    const d = max - min
    s = max === 0 ? 0 : d / max

    if (max === min) {
        h = 0 // achromatic
    } else {
        switch (max) {
            case r: h = (g - b) / d + (g < b ? 6 : 0); break
            case g: h = (b - r) / d + 2; break
            case b: h = (r - g) / d + 4; break
        }
        h /= 6
    }

    return { h: h * 360, s: s * 100, v: v * 100 }
}

// HSV to RGB
export function hsvToRgb(h, s, v) {
    let r, g, b
    const i = Math.floor(h * 6)
    const f = h * 6 - i
    const p = v * (1 - s)
    const q = v * (1 - f * s)
    const t = v * (1 - (1 - f) * s)

    switch (i % 6) {
        case 0: r = v, g = t, b = p; break
        case 1: r = q, g = v, b = p; break
        case 2: r = p, g = v, b = t; break
        case 3: r = p, g = q, b = v; break
        case 4: r = t, g = p, b = v; break
        case 5: r = v, g = p, b = q; break
    }

    return {
        r: Math.round(r * 255),
        g: Math.round(g * 255),
        b: Math.round(b * 255)
    }
}

// Parse Color String (supports hex, rgba) to {r,g,b,a}
export function parseColor(color) {
    if (!color) return { r: 255, g: 255, b: 255, a: 1 }

    if (color.startsWith('#')) {
        const rgb = hexToRgb(color)
        return rgb ? { ...rgb, a: 1 } : { r: 0, g: 0, b: 0, a: 1 }
    }

    if (color.startsWith('rgb')) {
        const parts = color.match(/[\d.]+/g)
        if (parts) {
            return {
                r: parseInt(parts[0]),
                g: parseInt(parts[1]),
                b: parseInt(parts[2]),
                a: parts[3] ? parseFloat(parts[3]) : 1
            }
        }
    }

    return { r: 0, g: 0, b: 0, a: 1 }
}
