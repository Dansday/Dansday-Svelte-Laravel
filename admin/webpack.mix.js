const mix = require('laravel-mix');

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