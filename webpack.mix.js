const mix = require('laravel-mix');

mix.setPublicPath('./public');

mix.js('resources/js/app.js', 'public/modules/uploadfile/js')
    .sass('resources/sass/app.scss', 'public/modules/uploadfile/css');
