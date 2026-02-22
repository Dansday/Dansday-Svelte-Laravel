const mix = require('laravel-mix');

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

mix.sass('resources/sass/admin.scss', 'assets/admin/css')
    .options({
        autoprefixer: {
            options: {
                browsers: [
                    "last 1 version",
                    "> 1%",
                    "IE 10"
                ]
            }
        },
        processCssUrls: false
    })
    .sourceMaps();