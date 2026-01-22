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
        const { type, condition, value, key } = rule;
        const user = context?.user || null;
        const post = context?.post || null;

        switch (type) {
            case 'auth':
                if (condition === 'logged_in') return !!user;
                if (condition === 'guest') return !user;
                return true;

            case 'role':
                if (!user || !user.roles) return condition === 'is_not';
                const hasRole = user.roles.some(r => r.name === value || r.slug === value);
                return condition === 'is' ? hasRole : !hasRole;

            case 'post_type':
                if (!post) return false;
                return condition === 'is' ? post.post_type === value : post.post_type !== value;

            case 'post_category':
                if (!post || !post.categories) return false;
                const hasCat = post.categories.some(c => c.slug === value || c.name === value || String(c.id) === String(value));
                return condition === 'is' ? hasCat : !hasCat;

            case 'post_tag':
                if (!post || !post.tags) return false;
                const hasTag = post.tags.some(t => t.slug === value || t.name === value || String(t.id) === String(value));
                return condition === 'is' ? hasTag : !hasTag;

            case 'author':
                if (!post || !post.author) return false;
                const isAuthor = String(post.author.id) === String(value) || post.author.user_nicename === value;
                return condition === 'is' ? isAuthor : !isAuthor;

            case 'date_time': {
                const now = new Date();
                if (key === 'day_of_week') {
                    const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                    const currentDay = days[now.getDay()];
                    return condition === 'is' ? currentDay === value : currentDay !== value;
                }
                const targetDate = new Date(value);
                if (condition === 'before') return now < targetDate;
                if (condition === 'after') return now > targetDate;
                return true;
            }

            case 'date_archive':
                return window.location.pathname.includes('/archive/') || !!window.location.pathname.match(/\/\d{4}\/\d{2}/);

            case 'tag_page':
                return window.location.pathname.includes('/tag/');
            case 'category_page':
                return window.location.pathname.includes('/category/');
            case 'search_results':
                return new URLSearchParams(window.location.search).has('s');

            case 'post_meta':
                if (!post || !post.meta) return false;
                const metaValue = post.meta[key];
                if (condition === 'is') return String(metaValue) === String(value);
                if (condition === 'is_not') return String(metaValue) !== String(value);
                if (condition === 'contains') return String(metaValue).includes(String(value));
                return true;

            case 'url_param':
                const params = new URLSearchParams(window.location.search);
                const paramValue = params.get(key);
                if (condition === 'exists') return params.has(key);
                if (condition === 'is') return paramValue === String(value);
                return true;

            case 'cookie':
                const cookies = document.cookie.split(';').reduce((acc, c) => {
                    const [k, v] = c.split('=').map(s => s.trim());
                    acc[k] = v;
                    return acc;
                }, {});
                const cookieValue = cookies[key];
                if (condition === 'exists') return key in cookies;
                if (condition === 'is') return cookieValue === String(value);
                return true;

            case 'browser':
                const ua = navigator.userAgent.toLowerCase();
                if (value === 'chrome') return ua.includes('chrome') && !ua.includes('edg');
                if (value === 'safari') return ua.includes('safari') && !ua.includes('chrome');
                if (value === 'firefox') return ua.includes('firefox');
                if (value === 'edge') return ua.includes('edg');
                return true;

            case 'os':
                const pf = navigator.platform.toLowerCase();
                if (value === 'mac') return pf.includes('mac');
                if (value === 'windows') return pf.includes('win');
                if (value === 'linux') return pf.includes('linux');
                if (value === 'ios') return /iphone|ipad|ipod/.test(ua);
                if (value === 'android') return /android/.test(ua);
                return true;

            default:
                return true;
        }
    }
}
