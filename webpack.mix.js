const mix = require('laravel-mix');

mix.setPublicPath('./public');

mix.js('resources/js/module.js', 'public/modules/uploadfile/js')
    .sass('resources/sass/module.scss', 'public/modules/uploadfile/css');
