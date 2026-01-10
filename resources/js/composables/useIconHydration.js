import { render, h } from 'vue';
import * as LucideIcons from 'lucide-vue-next';

export function useIconHydration() {
    const hydrateIcons = (container) => {
        if (!container) return;

        const icons = container.querySelectorAll('span[data-type="icon"]');

        icons.forEach(el => {
            // Check if already hydrated
            if (el.dataset.hydrated) return;

            const name = el.getAttribute('name');
            const IconComponent = LucideIcons[name] || LucideIcons.Circle;

            // Collect styles from attributes
            // Note: attributes in HTML might be lowercase, but Tiptap should preserve them if possible
            // We'll check standard names
            const size = el.getAttribute('size') || '1em';
            const color = el.getAttribute('color') || 'currentColor';
            const strokeWidth = el.getAttribute('strokeWidth') || el.getAttribute('strokewidth') || 2;
            const rotate = el.getAttribute('rotate') || 0;
            const backgroundColor = el.getAttribute('backgroundColor') || el.getAttribute('backgroundcolor');
            const borderRadius = el.getAttribute('borderRadius') || el.getAttribute('borderradius') || '0px';
            const padding = el.getAttribute('padding') || '0px';
            const opacity = el.getAttribute('opacity') || 1;

            // Apply wrapper styles to the span itself
            el.style.display = 'inline-block';
            el.style.verticalAlign = 'middle';
            el.style.lineHeight = '0'; // Fix vertical alignment issues
            if (backgroundColor) el.style.backgroundColor = backgroundColor;
            if (borderRadius) el.style.borderRadius = borderRadius;
            if (padding) el.style.padding = padding;
            if (rotate) el.style.transform = `rotate(${rotate}deg)`;
            if (opacity) el.style.opacity = opacity;

            // Create vnode
            const vnode = h(IconComponent, {
                size: size,
                color: color,
                strokeWidth: strokeWidth,
            });

            // Render into the element
            render(vnode, el);

            // Mark as hydrated
            el.dataset.hydrated = 'true';
        });
    };

    return { hydrateIcons };
}
