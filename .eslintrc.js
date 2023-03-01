module.exports = {
    env: {
        browser: true,
        es6: true,
    },
    extends: ['eslint:recommended', 'plugin:vue/recommended', 'prettier'],
    plugins: ['prettier'],
    'prettier/prettier': ['error', { endOfLine: 'lf' }],
    globals: {
        Atomics: 'readonly',
        SharedArrayBuffer: 'readonly',
    },
    parserOptions: {
        ecmaVersion: 2018,
        sourceType: 'module',
    },
    rules: {
        indent: ['error', 4],
        'vue/multi-word-component-names': 'off',
        'prettier/prettier': 'warn',
    },
    ignorePatterns: ['.eslintrc.js', 'webpack.mix.js', 'vendor/', 'public/', 'notes/'],
}
