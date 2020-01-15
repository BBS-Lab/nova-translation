<template>
  <div class="nova-translation-field_index" v-if="field.value">
    <div v-for="locale in field.locales" :key="`translation_${locale.id}`" v-html="viewLink(locale)" v-if="locale.id !== field.value.locale_id"/>
  </div>
</template>

<script>
  import I18nMixin from '../../mixins/I18n'
  import TranslationMixin from '../../mixins/Translation'

  export default {
    mixins: [
      I18nMixin,
      TranslationMixin,
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
        return `<a href="${this.basePath()}/resources/${this.resourceName}/${this.field.translations[locale.id].translatable_id}"><span class="nova-translation--flag">${this.flag(locale)}</span></a>`
      },
    },
  }
</script>

<style lang="scss">
  .nova-translation-field_index {
    display: flex;
    align-items: center;

    div {
      margin-left: .5rem;

      &:first-child {
        margin-left: 0;
      }

      a {
        text-decoration: none;
      }
    }
  }
</style>
