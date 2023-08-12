module.exports = {
    purge: {
        content: [
            './resources/**/*.vue',
            './resources/js/**/*.js',
            './resources/views/**/*.blade.php',
            './resources/views/**/*.twig',
        ],
        safelist: [
            'text-red-600',
            'text-green-600',
            'border-red-600',
            'border-green-600',
        ],
    },
    theme: {
        extend: {
            colors: {
                orange: {
                    200: '#ffe4cc',
                    600: '#FF7700',
                    // from tailwind v1
                    100: '#fffaf0',
                    300: '#fbd38d',
                    500: '#ed8936',
                    700: '#c05621',
                },
                gray: {
                    100: '#fbfbfb',
                    200: '#f3f3f3',
                    800: '#1e242d',
                    900: '#14181d',
                    // from tailwind v1
                    300: '#e2e8f0',
                    400: '#cbd5e0',
                    500: '#a0aec0',
                    600: '#718096',
                    700: '#4a5568',
                },
                blue: {
                    // from tailwind v1
                    600: '#3182ce',
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
