export default {
  methods: {
    flag(locale) {
      return this.trans(`Flag ${locale.iso.toUpperCase()}`)
    },
  },
}
