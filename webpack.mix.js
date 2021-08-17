const mix = require('laravel-mix');

mix.setPublicPath('./public');

mix.js('resources/js/module.js', 'public/modules/uploadfile/js')
   .js('resources/js/components.js', 'public/modules/uploadfile/js')
    .sass('resources/sass/module.scss', 'public/modules/uploadfile/css');

if(!mix.inProduction()) {
    mix.sourceMaps();
}

mix.webpackConfig({
    externals: {
        '@bristol-su/frontend-toolkit': 'Toolkit',
    }
});
