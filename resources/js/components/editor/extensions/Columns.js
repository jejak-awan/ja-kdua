import { Node, mergeAttributes } from '@tiptap/core'

export const Columns = Node.create({
    name: 'columns',
    group: 'block',
    content: 'column+',

    addAttributes() {
        return {
            count: {
                default: 2,
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-type="columns"]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(HTMLAttributes, { 'data-type': 'columns', class: 'columns-container' }), 0]
    },

    addCommands() {
        return {
            insertColumns: ({ count = 2 } = {}) => ({ commands }) => {
                const columns = Array.from({ length: count }, () => ({
                    type: 'column',
                    content: [{ type: 'paragraph' }],
                }))

                return commands.insertContent({
                    type: 'columns',
                    attrs: { count },
                    content: columns,
                })
            },
        }
    },
})
