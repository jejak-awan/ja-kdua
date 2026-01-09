import { Node, mergeAttributes } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import ShapeComponent from '../nodes/ShapeComponent.vue';

export const Shape = Node.create({
    name: 'shape',

    group: 'block',

    content: 'block+',

    draggable: true,

    addAttributes() {
        return {
            width: {
                default: '300px',
            },
            height: {
                default: '200px',
            },
            backgroundColor: {
                default: '#e2e8f0', // slate-200 equivalent
            },
            borderColor: {
                default: null,
            },
            borderWidth: {
                default: '0',
            },
            borderRadius: {
                default: '4px',
            },
            textAlign: {
                default: 'center',
            },
            color: {
                default: 'inherit',
            },
            boxShadow: {
                default: 'none',
            }
        };
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-type="shape"]',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(HTMLAttributes, { 'data-type': 'shape' }), 0];
    },

    addNodeView() {
        return VueNodeViewRenderer(ShapeComponent);
    },
});
