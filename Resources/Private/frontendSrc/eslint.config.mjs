import eslint from '@eslint/js';
import importPlugin from 'eslint-plugin-import';
import eslintPluginPrettier from 'eslint-plugin-prettier/recommended';
import simpleImportSort from 'eslint-plugin-simple-import-sort';
import globals from 'globals';

export default [
    {
        ignores: ['dist/**', 'plugins/SvgSpritePlugin/example/**'],
    },
    eslint.configs.recommended,
    eslintPluginPrettier,
    {
        plugins: {
            import: importPlugin,
            'simple-import-sort': simpleImportSort,
        },
        languageOptions: {
            ecmaVersion: 2021,
            sourceType: 'module',
            globals: {
                ...globals.browser,
                ...globals.node,
                ...globals.jquery,
            },
        },
        rules: {
            'no-console': 'off',
            'no-debugger': 'off',
            'no-unused-vars': 'warn',
            'import/no-unresolved': 'error',
            'simple-import-sort/imports': 'error',
            'simple-import-sort/exports': 'error',
        },
    },
];
