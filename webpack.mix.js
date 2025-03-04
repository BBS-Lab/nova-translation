const mix = require('laravel-mix')
const postcssImport = require('postcss-import')
const tailwindcss = require('tailwindcss')

mix.extend('nova', new require('laravel-nova-devtool'))
const config = require('./webpack.config')

mix
  .disableNotifications()
  .setPublicPath('dist')
  .js('resources/js/nova-translation.js', 'js')
  .vue({ version: 3 })
  .postCss('resources/css/tool.css', 'css', [postcssImport(), tailwindcss('tailwind.config.js')])
  .options({
    processCssUrls: false,
  })
  .webpackConfig(config)
  .nova('bbs-lab/nova-translation')
