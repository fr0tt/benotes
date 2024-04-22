module.exports = {
    env: {
        browser: true,
        es6: true,
    },
    extends: ['eslint:recommended', 'plugin:vue/recommended', 'prettier'],
    plugins: ['prettier'],
    globals: {
        Atomics: 'readonly',
        SharedArrayBuffer: 'readonly',
    },
    parserOptions: {
        ecmaVersion: 2018,
        sourceType: 'module',
    },
    rules: {
        indent: ['warn', 4],
        'vue/multi-word-component-names': 'off',
        'vue/no-unused-components': 'warn',
        'vue/no-unused-vars': 'warn',
        'prettier/prettier': 'warn',
    },
    ignorePatterns: ['.eslintrc.js', 'webpack.mix.js', 'vendor/', 'public/', 'notes/'],
}
