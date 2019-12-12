<template>
  <div class="border-b border-40">
    <div class="px-8 py-4 flex items-center justify-between">
      <div>
        <span class="text-4xl" v-if="field.value" v-html="flag(field.locales[field.value.locale_id])"/>
      </div>
      <div class="flex items-center text-right">
        <div
          class="inline-block ml-2"
          v-for="translation in field.translations" :key="`translation_${translation.locale_id}`"
          v-html="editLink(translation)" v-if="translation.locale_id !== field.value.locale_id"
        />
      </div>
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
        return `<a class="no-underline" href="/resources/${this.resourceName}/${translation.translatable_id}/edit"><span class="nova-translation--flag">${this.flag(this.field.locales[translation.locale_id])}</span></a>`
      },
    },
  }
</script>
