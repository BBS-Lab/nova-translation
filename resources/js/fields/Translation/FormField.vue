<template>
  <div class="nova-translation">
    <div>
      <span class="nova-translation--flag_current" v-html="flag(field.locales[field.value.locale_id])"/>
    </div>
    <div class="nova-translation--links">
      <div v-for="translation in field.translations" :key="`translation_${translation.locale_id}`" v-html="editLink(translation)" v-if="translation.locale_id !== field.value.locale_id"/>
    </div>
  </div>
</template>

<script>
  import I18nMixin from '../../mixins/I18n'
  import TranslationMixin from '../../mixins/Translation'
  import { FormField, HandlesValidationErrors } from 'laravel-nova'

  export default {
    mixins: [
      I18nMixin,
      TranslationMixin,
      FormField,
      HandlesValidationErrors,
    ],

    props: [
      'field',
      'resourceId',
      'resourceName',
    ],

    methods: {
      setInitialValue() {
        this.value = ''
        if (this.field.value) {
          this.value = `${this.field.value.locale_id}|${this.field.value.translation_id}`
        }
      },

      fill(formData) {
        // formData.append(this.field.attribute, this.value)
      },

      editLink(translation) {
        return `<a href="/resources/${this.resourceName}/${translation.translatable_id}/edit"><span class="nova-translation--flag">${this.flag(this.field.locales[translation.locale_id])}</span></a>`
      },
    },
  }
</script>

<style lang="scss">
  .nova-translation {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: .75rem;
    padding-left: 2rem;
    padding-right: 2rem;

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
        font-size: 1rem;
        text-decoration: none;
      }
    }
  }
</style>
