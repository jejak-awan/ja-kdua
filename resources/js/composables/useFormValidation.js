import { ref, reactive } from 'vue';
import { useI18n } from 'vue-i18n';

/**
 * Form validation composable for consistent validation across all forms
 * Supports both synchronous and asynchronous validation rules
 */
export function useFormValidation() {
    const { t } = useI18n();
    const errors = ref({});
    const touched = ref({});
    const validating = ref(false);

    /**
     * Built-in validation rules
     */
    const rules = {
        required: (value) => {
            const valid = value !== null && value !== undefined && value !== '' &&
                (Array.isArray(value) ? value.length > 0 : true);
            return valid || 'common.validation.required';
        },

        email: (value) => {
            if (!value) return true; // Skip if empty (use required separately)
            const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            return valid || 'common.validation.email';
        },

        min: (min) => (value) => {
            if (!value) return true;
            const valid = String(value).length >= min;
            return valid || t('common.validation.min', { min });
        },

        max: (max) => (value) => {
            if (!value) return true;
            const valid = String(value).length <= max;
            return valid || t('common.validation.max', { max });
        },

        minValue: (min) => (value) => {
            if (!value) return true;
            const valid = Number(value) >= min;
            return valid || t('common.validation.minValue', { min });
        },

        maxValue: (max) => (value) => {
            if (!value) return true;
            const valid = Number(value) <= max;
            return valid || t('common.validation.maxValue', { max });
        },

        numeric: (value) => {
            if (!value) return true;
            const valid = /^\d+$/.test(value);
            return valid || 'common.validation.numeric';
        },

        url: (value) => {
            if (!value) return true;
            try {
                new URL(value);
                return true;
            } catch {
                return 'common.validation.url';
            }
        },

        confirmed: (confirmField) => (value, formData) => {
            const valid = value === formData[confirmField];
            return valid || 'common.validation.confirmed';
        },

        between: (min, max) => (value) => {
            if (!value) return true;
            const len = String(value).length;
            const valid = len >= min && len <= max;
            return valid || t('common.validation.between', { min, max });
        },

        in: (allowedValues) => (value) => {
            if (!value) return true;
            const valid = allowedValues.includes(value);
            return valid || 'common.validation.in';
        },

        alpha: (value) => {
            if (!value) return true;
            const valid = /^[a-zA-Z]+$/.test(value);
            return valid || 'common.validation.alpha';
        },

        alphaNum: (value) => {
            if (!value) return true;
            const valid = /^[a-zA-Z0-9]+$/.test(value);
            return valid || 'common.validation.alphaNum';
        },

        slug: (value) => {
            if (!value) return true;
            const valid = /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(value);
            return valid || 'common.validation.slug';
        },
    };

    /**
     * Validate a single field
     */
    const validateField = (fieldName, value, fieldRules, formData = {}) => {
        if (!fieldRules || fieldRules.length === 0) {
            return true;
        }

        const fieldErrors = [];

        for (const rule of fieldRules) {
            let validator;
            let result;

            if (typeof rule === 'string') {
                // Simple rule name: 'required', 'email', etc.
                validator = rules[rule];
                if (validator) {
                    result = validator(value, formData);
                }
            } else if (typeof rule === 'object') {
                // Rule with parameters: { min: 8 }, { between: [3, 10] }
                const ruleName = Object.keys(rule)[0];
                const ruleParams = rule[ruleName];
                validator = rules[ruleName];

                if (validator) {
                    if (Array.isArray(ruleParams)) {
                        result = validator(...ruleParams)(value, formData);
                    } else {
                        result = validator(ruleParams)(value, formData);
                    }
                }
            } else if (typeof rule === 'function') {
                // Custom validation function
                result = rule(value, formData);
            }

            // If validation failed, add error
            if (result !== true) {
                const errorMessage = typeof result === 'string' ? t(result) : result;
                fieldErrors.push(errorMessage);
            }
        }

        // Update errors for this field
        if (fieldErrors.length > 0) {
            errors.value[fieldName] = fieldErrors;
            return false;
        } else {
            delete errors.value[fieldName];
            return true;
        }
    };

    /**
     * Validate entire form
     */
    const validate = (formData, validationRules) => {
        validating.value = true;
        errors.value = {};

        let isValid = true;

        for (const [fieldName, fieldRules] of Object.entries(validationRules)) {
            const fieldValue = formData[fieldName];
            const fieldValid = validateField(fieldName, fieldValue, fieldRules, formData);

            if (!fieldValid) {
                isValid = false;
            }
        }

        validating.value = false;
        return isValid;
    };

    /**
     * Clear all errors
     */
    const clearErrors = () => {
        errors.value = {};
        touched.value = {};
    };

    /**
     * Clear error for specific field
     */
    const clearError = (fieldName) => {
        delete errors.value[fieldName];
        delete touched.value[fieldName];
    };

    /**
     * Set errors from backend (422 response)
     */
    const setErrors = (backendErrors) => {
        errors.value = backendErrors;
    };

    /**
     * Mark field as touched
     */
    const touch = (fieldName) => {
        touched.value[fieldName] = true;
    };

    /**
     * Check if form has any errors
     */
    const hasErrors = () => {
        return Object.keys(errors.value).length > 0;
    };

    /**
     * Get error for specific field
     */
    const getError = (fieldName) => {
        return errors.value[fieldName]?.[0] || null;
    };

    /**
     * Check if field has error
     */
    const hasError = (fieldName) => {
        return !!errors.value[fieldName];
    };

    return {
        errors,
        touched,
        validating,
        rules,
        validate,
        validateField,
        clearErrors,
        clearError,
        setErrors,
        touch,
        hasErrors,
        hasError,
        getError,
    };
}
