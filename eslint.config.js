import vue from 'eslint-plugin-vue'
import prettier from 'eslint-config-prettier'
import js from '@eslint/js'
import pluginUnusedImports from 'eslint-plugin-unused-imports'

export default [
    js.configs.recommended,
    ...vue.configs['flat/recommended'],
    prettier,
    {
        files: ['**/*.{js,vue,ts}'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
        },
        plugins: {
            'unused-imports': pluginUnusedImports,
        },
        rules: {
            'unused-imports/no-unused-imports': 'error',
            'unused-imports/no-unused-vars': [
                'warn',
                {
                    vars: 'all',
                    varsIgnorePattern: '^_',
                    args: 'after-used',
                    argsIgnorePattern: '^_',
                },
            ],

            'vue/multi-word-component-names': 'off',
            'no-console': 'off',
            'no-debugger': 'off',
        },
    },
]
