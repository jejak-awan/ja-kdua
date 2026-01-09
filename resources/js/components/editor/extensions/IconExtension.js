import { Node, mergeAttributes } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import IconComponent from '../nodes/IconComponent.vue';

export const Icon = Node.create({
    name: 'icon',

    group: 'inline',

    inline: true,

    selectable: true,

    draggable: true,

    atom: true,

    addAttributes() {
        return {
            name: {
                default: 'Circle',
            },
            size: {
                default: '1em',
            },
            color: {
                default: 'currentColor',
            }
        };
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-type="icon"]',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['span', mergeAttributes(HTMLAttributes, { 'data-type': 'icon' })];
    },

    addNodeView() {
        return VueNodeViewRenderer(IconComponent);
    },
});
