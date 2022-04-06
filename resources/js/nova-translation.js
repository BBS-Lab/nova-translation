Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'nova-translation',
      path: '/nova-translation',
      component: require('./tools/TranslationMatrix/TranslationMatrix.vue').default
    }
  ])

  Vue.component('index-nova-translation-field', require('./fields/Translation/IndexField').default)
  Vue.component('detail-nova-translation-field', require('./fields/Translation/DetailField').default)
  Vue.component('form-nova-translation-field', require('./fields/Translation/FormField').default)
})
