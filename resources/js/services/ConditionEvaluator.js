/**
 * Logic to evaluate if a block should be visible based on settings
 */
export class ConditionEvaluator {
    /**
     * Evaluate if a block should be shown
     * @param {Object} block The block object containing settings and visibility rules
     * @param {Object} context App context (user, page, etc.)
     * @returns {Boolean}
     */
    static evaluate(block, context = {}) {
        // If no visibility settings, show by default
        if (!block.settings?.visibility_rules || !Array.isArray(block.settings.visibility_rules)) {
            return true;
        }

        const rules = block.settings.visibility_rules;
        if (rules.length === 0) return true;

        // Logic mode: 'all' (AND) or 'any' (OR)
        const mode = block.settings.visibility_mode || 'all';

        const results = rules.map(rule => this.evaluateRule(rule, context));

        if (mode === 'any') {
            return results.some(r => r === true);
        }

        return results.every(r => r === true);
    }

    static evaluateRule(rule, context) {
        const { type, condition, value } = rule;
        const user = context?.user || null;

        switch (type) {
            case 'auth':
                // condition: 'logged_in' or 'guest'
                if (condition === 'logged_in') return !!user;
                if (condition === 'guest') return !user;
                return true;

            case 'role':
                // condition: 'is' or 'is_not'
                if (!user || !user.roles) return condition === 'is_not';
                const hasRole = user.roles.some(r => r.name === value || r.slug === value);
                return condition === 'is' ? hasRole : !hasRole;

            case 'date':
                const now = new Date();
                const targetDate = new Date(value);
                if (condition === 'before') return now < targetDate;
                if (condition === 'after') return now > targetDate;
                return true;

            default:
                return true;
        }
    }
}
