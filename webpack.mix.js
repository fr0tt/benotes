const mix = require('laravel-mix')
const tailwindcss = require('tailwindcss')
require('laravel-mix-svg-vue')
const { InjectManifest } = require('workbox-webpack-plugin')

const dotenv = require('dotenv')
dotenv.config()

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

mix.disableNotifications()

/*

// does not work with current webpack version, results in empty css file in production

mix.babelConfig({
    plugins: ['@babel/plugin-syntax-dynamic-import']
})

mix.extract()

*/

mix.js('resources/js/app.js', 'public/js')
    .vue({ version: 2 })
    .sass('resources/sass/app.scss', 'public/css')
    .options({
        processCssUrls: false,
        postCss: [tailwindcss('./tailwind.config.js')]
    })
    .svgVue({
        svgoSettings: [
            { removeTitle: false },
            { removeViewBox: false },
            { removeDimensions: true }
        ]
    })
    .webpackConfig({
        plugins: [
            new InjectManifest({
                swSrc: './resources/js/service-worker.js'
            })
        ],
        output: {
            publicPath: ''
        }
    })
    .browserSync(process.env.APP_URL)