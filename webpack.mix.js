const mix = require('laravel-mix')
const postcssImport = require('postcss-import')
const tailwindcss = require('tailwindcss')
const path = require('path')

mix
  .disableNotifications()
  .setPublicPath('dist')
  .js('resources/js/nova-translation.js', 'js')
  .vue({ version: 3 })
  .postCss('resources/css/tool.css', 'css', [postcssImport(), tailwindcss('tailwind.config.js')])
  .options({
    processCssUrls: false,
  })
  .webpackConfig({
    externals: {
      vue: 'Vue',
    },
    output: {
      uniqueName: 'bbs-lab/nova-translation',
    },
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'resources/js/'),
      },
    },
  })
