Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'nova-translation',
      path: '/nova-translation',
      component: require('./components/TranslationMatrix.vue')
    }
  ])
})
