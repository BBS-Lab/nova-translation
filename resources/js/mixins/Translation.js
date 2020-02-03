export default {
  computed: {
    locale() {
      return Nova.config.locale
    },

    locales() {
      if (! this.field || ! this.field.locales) {
        return {}
      }

      return this.field.locales
    },

    otherLocales() {
      return _.pickBy(this.locales, l => l.id !== this.translation.locale_id)
    },

    translation() {
      if (! this.field || ! this.field.value) {
        return null
      }

      return this.field.value
    },

    translations() {
      if (! this.field || ! this.field.translations) {
        return {}
      }

      if (Array.isArray(this.field.translations)) {
        return {}
      }

      return this.field.translations
    },

    isTranslated() {
      return _.mapValues(this.locales, l => {
        if (this.translations.hasOwnProperty(l.id)) {
          return true
        }

        if (this.translation !== null && this.translation.locale_id === l.id) {
          return true
        }

        return false
      })
    }
  },
}
