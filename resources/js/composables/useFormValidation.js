import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

/**
 * Form validation composable with Zod schema support
 * Provides both client-side (Zod) and server-side (422) error handling
 * 
 * @param {Object} zodSchema - Optional Zod schema for client-side validation
 */
export function useFormValidation(zodSchema = null) {
    const { t } = useI18n();
    const errors = ref({});
    const touched = ref({});
    const validating = ref(false);

    /**
     * Built-in validation rules (legacy support)
     */
    const rules = {
        required: (value) => {
            const valid = value !== null && value !== undefined && value !== '' &&
                (Array.isArray(value) ? value.length > 0 : true);
            return valid || 'common.validation.required';
        },

        email: (value) => {
            if (!value) return true;
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

        slug: (value) => {
            if (!value) return true;
            const valid = /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(value);
            return valid || 'common.validation.slug';
        },
    };

    /**
     * Format Zod errors to match existing error structure
     * { fieldName: ['error message 1', 'error message 2'] }
     */
    const formatZodErrors = (zodError) => {
        const formatted = {};
        for (const issue of zodError.issues) {
            const path = issue.path.join('.') || '_root';
            if (!formatted[path]) formatted[path] = [];
            formatted[path].push(issue.message);
        }
        return formatted;
    };

    /**
     * Validate form data using Zod schema
     * @param {Object} formData - Form data to validate
     * @returns {boolean} - True if valid, false if validation errors
     */
    const validateWithZod = (formData) => {
        if (!zodSchema) {
            console.warn('useFormValidation: No Zod schema provided');
            return true;
        }

        validating.value = true;
        errors.value = {};

        try {
            const result = zodSchema.safeParse(formData);

            if (!result.success) {
                errors.value = formatZodErrors(result.error);
                validating.value = false;
                return false;
            }

            validating.value = false;
            return true;
        } catch (error) {
            console.error('Zod validation error:', error);
            validating.value = false;
            return true; // Allow form submission on schema error
        }
    };

    /**
     * Validate a single field using Zod schema
     * @param {string} fieldName - Field name to validate
     * @param {any} value - Field value
     * @param {Object} formData - Full form data (for cross-field validation)
     */
    const validateFieldWithZod = (fieldName, value, formData = {}) => {
        if (!zodSchema) return true;

        // Create partial data for validation
        const dataToValidate = { ...formData, [fieldName]: value };

        try {
            const result = zodSchema.safeParse(dataToValidate);

            if (!result.success) {
                // Only extract errors for this field
                const fieldErrors = result.error.issues
                    .filter(issue => issue.path[0] === fieldName)
                    .map(issue => issue.message);

                if (fieldErrors.length > 0) {
                    errors.value[fieldName] = fieldErrors;
                    return false;
                }
            }

            // Clear error for this field if valid
            delete errors.value[fieldName];
            return true;
        } catch {
            return true;
        }
    };

    /**
     * Validate a single field using legacy rules
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
                validator = rules[rule];
                if (validator) {
                    result = validator(value, formData);
                }
            } else if (typeof rule === 'object') {
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
                result = rule(value, formData);
            }

            if (result !== true) {
                const errorMessage = typeof result === 'string' ? t(result) : result;
                fieldErrors.push(errorMessage);
            }
        }

        if (fieldErrors.length > 0) {
            errors.value[fieldName] = fieldErrors;
            return false;
        } else {
            delete errors.value[fieldName];
            return true;
        }
    };

    /**
     * Validate entire form using legacy rules
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
        errors.value = backendErrors || {};
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
     * Get error for specific field (first error only)
     */
    const getError = (fieldName) => {
        const fieldErrors = errors.value[fieldName];
        if (Array.isArray(fieldErrors)) return fieldErrors[0] || null;
        return fieldErrors || null;
    };

    /**
     * Check if field has error
     */
    const hasError = (fieldName) => {
        return !!errors.value[fieldName];
    };

    return {
        // State
        errors,
        touched,
        validating,

        // Legacy rules
        rules,

        // Zod validation
        validateWithZod,
        validateFieldWithZod,

        // Legacy validation
        validate,
        validateField,

        // Error management
        clearErrors,
        clearError,
        setErrors,

        // Touch tracking
        touch,

        // Helpers
        hasErrors,
        hasError,
        getError,
    };
}
