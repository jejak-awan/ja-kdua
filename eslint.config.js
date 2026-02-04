import js from '@eslint/js';
import vue from 'eslint-plugin-vue';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default [
    {
        ignores: ['node_modules/**', 'public/build/**', 'vendor/**'],
    },
    js.configs.recommended,
    ...tseslint.configs.recommended,
    ...vue.configs['flat/recommended'],
    {
        files: ['resources/js/**/*.vue'],
        languageOptions: {
            parserOptions: {
                parser: tseslint.parser,
            },
        },
    },
    {
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.node,
            },
            ecmaVersion: 2022,
            sourceType: 'module',
        },
        files: ['resources/js/**/*.{js,ts,vue}'],
        rules: {
            // Vue Rules
            'vue/multi-word-component-names': 'off',
            'vue/no-v-html': 'off', // CMS content requires v-html
            'vue/html-indent': 'off',
            'vue/singleline-html-element-content-newline': 'off',
            'vue/max-attributes-per-line': 'off',
            'vue/attributes-order': 'off',
            'vue/html-self-closing': 'off',
            'vue/no-multiple-template-root': 'off',

            // TS Rules
            '@typescript-eslint/no-explicit-any': 'warn',
            '@typescript-eslint/no-unused-vars': ['warn', {
                argsIgnorePattern: '^_',
                varsIgnorePattern: '^_',
                caughtErrorsIgnorePattern: '^_'
            }],
            '@typescript-eslint/consistent-type-imports': ['warn', {
                prefer: 'type-imports',
                disallowTypeAnnotations: false,
            }],

            // Import Rules
            'no-restricted-imports': ['error', {
                paths: [{
                    name: 'lucide-vue-next',
                    message: 'Import dari lucide-vue-next/dist/esm/icons/[icon-name].js untuk tree-shaking',
                }],
            }],

            // General
            'no-console': ['warn', { allow: ['warn', 'error'] }],
            'no-undef': 'off', // TS handles this
            'no-useless-escape': 'off',
        },
    },
];

