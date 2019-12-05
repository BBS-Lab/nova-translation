<template>
  <div class="nova-translation" v-if="field.value">
    <div>
      <span class="nova-translation--flag_current" v-html="trans(`Flag ${field.locales[field.value.locale_id].iso.toUpperCase()}`)"/>
    </div>
    <div class="nova-translation--links">
      <div v-for="translation in field.translations" :key="`translation_${translation.locale_id}`" v-html="viewLink(translation)" v-if="translation.locale_id !== field.value.locale_id"/>
    </div>
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
      'resource',
      'resourceId',
      'resourceName',
    ],

    methods: {
      viewLink(translation) {
        return `<a href="/resources/${this.resourceName}/${translation.translatable_id}"><span class="nova-translation--flag">${this.flag(this.field.locales[translation.locale_id])}</span></a>`
      },
    },
  }
</script>

<style lang="scss">
  .nova-translation {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0;

    &--flag_current {
      font-size: 1.5rem;
    }

    &--links {
      display: flex;
      align-items: center;
      text-align: right;

      a {
        display: inline-block;
        margin-left: .5rem;
        text-decoration: none;
      }
    }
  }
</style>
