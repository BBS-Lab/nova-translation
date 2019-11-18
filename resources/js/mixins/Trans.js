export default {
  methods: {
    trans(key, replace) {
      return window.config.translations[`nova-translation::${key}`]
        ? this.__(`nova-translation::${key}`, replace)
        : key
    }
  }
}
