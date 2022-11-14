export default {
  methods: {
    trans(key, replace) {
      return Nova.config('translations')[`nova-translation::${key}`]
        ? this.__(`nova-translation::${key}`, replace)
        : key
    }
  }
}
