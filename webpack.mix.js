const mix = require('laravel-mix')

mix
  .setPublicPath('dist')
  .js('resources/js/nova-translation.js', 'js')
  .sass('resources/sass/nova-translation.scss', 'css')
