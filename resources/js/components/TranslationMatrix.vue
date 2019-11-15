<template>
  <div>
    <h3>{{ trans('Translations Matrix') }}</h3>
    <loading-view v-if="loading">Coucou</loading-view>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        matrix: {},
        loading: false
      }
    },

    mounted() {
      console.log('Nova Translation Matrix mounted!')
      this.loadMatrix()
    },

    methods: {
      loadMatrix() {
        Nova.request().get('/nova-vendor/nova-translation/locales').then((response) => {
          console.log(response)
        }).catch((error) => {
          console.error(error)
        })
      },

      trans(key, replace) {
        return window.config.translations[`nova-translation::${key}`]
          ? this.__(`nova-translation::${key}`, replace)
          : key
      }
    }
  }
</script>
