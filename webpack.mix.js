const mix = require('laravel-mix')
const tailwindcss = require('tailwindcss')
require('laravel-mix-purgecss')
require('laravel-mix-svg-vue')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.disableSuccessNotifications()

/*

// does not work with current webpack version, results in empty css file in production

mix.babelConfig({
    plugins: ['@babel/plugin-syntax-dynamic-import']
})

mix.extract()

*/

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .options({
        processCssUrls: false,
        postCss: [tailwindcss('./tailwind.config.js')]
    })
    .purgeCss()
    .svgVue()

