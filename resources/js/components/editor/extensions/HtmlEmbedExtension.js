import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import HtmlEmbedComponent from '../nodes/HtmlEmbedComponent.vue'

export const HtmlEmbed = Node.create({
    name: 'htmlEmbed',

    group: 'block',

    atom: true,

    addAttributes() {
        return {
            html: {
                default: '',
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-html-embed]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes({ 'data-html-embed': '' }, HTMLAttributes)]
    },

    addNodeView() {
        return VueNodeViewRenderer(HtmlEmbedComponent)
    },

    addCommands() {
        return {
            insertHtmlEmbed: (html) => ({ commands }) => {
                return commands.insertContent({
                    type: this.name,
                    attrs: { html },
                })
            },
        }
    },
})
