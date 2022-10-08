const { colors } = require('tailwindcss/defaultTheme')

module.exports = {
    purge: {
        content: [
            './resources/**/*.vue',
            './resources/js/**/*.js',
            './resources/views/**/*.blade.php',
            './resources/views/**/*.twig',
        ],
    },
    theme: {
        extend: {
            colors: {
                orange: {
                    ...colors.orange,
                    600: '#FF7700',
                    200: '#ffe4cc',
                },
                gray: {
                    ...colors.gray,
                    100: '#fbfbfb',
                    200: '#f3f3f3',
                },
            },
            fontFamily: {
                mono: [
                    'JetBrains Mono',
                    'Fira Code',
                    'Cascadia Code',
                    'Consolas',
                    'Courier New',
                    'monospace',
                ],
            },
            spacing: {
                1.5: '0.375rem',
                7: '1.75rem',
                80: '20rem',
            },
        },
    },
    variants: {},
    plugins: [],
}
