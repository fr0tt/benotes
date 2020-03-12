const { colors } = require('tailwindcss/defaultTheme')

module.exports = {
    theme: {
        extend: {
            colors: {
                orange: {
                    ...colors.orange,
                    '600': '#FF7700',
                    '200': '#ffe4cc'
                }
            }
        }
    },
    variants: {},
    plugins: []
}
