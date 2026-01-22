import { reactive } from 'vue'

/**
 * InteractionManager
 * Handles cross-block triggers and reactive actions.
 */
class InteractionManager {
    constructor() {
        this.state = reactive({
            activeBlocks: new Set(),
            blockStates: {}, // Specific states per block
            lastTrigger: null
        })
    }

    /**
     * Trigger an action for a specific target block
     * @param {string} trigger - The event that triggered this (e.g., 'click', 'hover')
     * @param {string} action - The action to perform (e.g., 'toggle-class', 'play-animation')
     * @param {string} targetId - The ID of the block to affect
     * @param {Object} params - Additional parameters for the action
     */
    triggerAction(trigger, action, targetId, params = {}) {
        this.state.lastTrigger = { trigger, action, targetId, params, timestamp: Date.now() }

        switch (action) {
            case 'toggle-class':
                this.toggleClass(targetId, params.className || 'is-active')
                break
            case 'add-class':
                this.addClass(targetId, params.className || 'is-active')
                break
            case 'remove-class':
                this.removeClass(targetId, params.className || 'is-active')
                break
            case 'play-animation':
                this.playAnimation(targetId, params.animationName)
                break
            default:
                console.warn(`[InteractionManager] Unknown action: ${action}`)
        }
    }

    toggleClass(targetId, className) {
        if (!this.state.blockStates[targetId]) {
            this.state.blockStates[targetId] = { classes: [] }
        }
        const classes = this.state.blockStates[targetId].classes
        const index = classes.indexOf(className)
        if (index > -1) {
            classes.splice(index, 1)
        } else {
            classes.push(className)
        }
    }

    addClass(targetId, className) {
        if (!this.state.blockStates[targetId]) {
            this.state.blockStates[targetId] = { classes: [] }
        }
        if (!this.state.blockStates[targetId].classes.includes(className)) {
            this.state.blockStates[targetId].classes.push(className)
        }
    }

    removeClass(targetId, className) {
        if (this.state.blockStates[targetId]) {
            const index = this.state.blockStates[targetId].classes.indexOf(className)
            if (index > -1) {
                this.state.blockStates[targetId].classes.splice(index, 1)
            }
        }
    }

    playAnimation(targetId, animationName) {
        // We set a temporary animation flag that can be picked up by BaseBlock
        if (!this.state.blockStates[targetId]) {
            this.state.blockStates[targetId] = {}
        }
        this.state.blockStates[targetId].activeAnimation = animationName

        // Reset after duration (theoretical, would need actual duration or CSS event)
        setTimeout(() => {
            if (this.state.blockStates[targetId].activeAnimation === animationName) {
                this.state.blockStates[targetId].activeAnimation = null
            }
        }, 1000)
    }

    /**
     * Get reactive state for a specific block
     */
    getBlockState(blockId) {
        if (!this.state.blockStates[blockId]) {
            this.state.blockStates[blockId] = {
                classes: [],
                activeAnimation: null
            }
        }
        return this.state.blockStates[blockId]
    }
}

export const interactionManager = new InteractionManager()
