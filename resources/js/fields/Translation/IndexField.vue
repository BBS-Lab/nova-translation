<template>
  <div class="nova-translation-field_index" v-if="field.value">
    <div v-for="(locale, localeId) in field.locales" :key="localeId" v-html="viewLink(locale)" v-if="locale.id !== field.value.locale_id"/>
  </div>
</template>

<script>
  import I18n from '../../mixins/I18n'

  export default {
    mixins: [
      I18n,
    ],

    props: [
      'field',
      'resourceName',
    ],

    mounted() {
      //
    },

    methods: {
      viewLink(locale) {
        return `<a href="/resources/${this.resourceName}/${this.field.translations[locale.id].translatable_id}">${this.flag(locale)}</a>`
      },

      flag(locale) {
        return this.trans(`Flag ${locale.iso.toUpperCase()}`)
      },
    },
  }
</script>

<style>
  .nova-translation-field_index {
    display: flex;
    align-items: center;
  }

  .nova-translation-field_index div {
    margin-left: 8px;
  }

  .nova-translation-field_index div:first-child {
    margin-left: 0;
  }

  .nova-translation-field_index div a {
    text-decoration: none;
  }
</style>
