import Paragraph from '@tiptap/extension-paragraph'

export const Dropcap = Paragraph.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            dropcap: {
                default: false,
                parseHTML: element => element.classList.contains('has-dropcap'),
                renderHTML: attributes => {
                    if (!attributes.dropcap) {
                        return {}
                    }

                    return {
                        class: 'has-dropcap',
                    }
                },
            },
        }
    },

    addCommands() {
        return {
            toggleDropcap: () => ({ commands }) => {
                return commands.updateAttributes('paragraph', {
                    dropcap: !this.editor.getAttributes('paragraph').dropcap,
                })
            },
            setDropcap: (value = true) => ({ commands }) => {
                return commands.updateAttributes('paragraph', {
                    dropcap: value,
                })
            },
        }
    },
})
