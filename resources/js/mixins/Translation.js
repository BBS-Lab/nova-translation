export default {
  methods: {
    flag(locale) {
      return this.trans(`Flag ${locale.iso.toUpperCase()}`)
    },
    basePath() {
      return (window.config.base === '/') ? '' : window.config.base
    },
  },
}
