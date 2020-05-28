<template>
  <div class="border-b border-40">
    <div class="px-8 py-4 flex items-center justify-between">
      <heading v-if="isCreate">{{ locale.label }}</heading>
      <heading v-else-if="field.value">{{ field.locales[field.value.locale_id].label }}</heading>
    </div>
  </div>
</template>

<script>
import I18nMixin from '../../mixins/I18n'
import TranslationMixin from '../../mixins/Translation'
import CreateTranslationLink from './CreateTranslationLink'
import { FormField, HandlesValidationErrors } from 'laravel-nova'

export default {
  components: {
    CreateTranslationLink,
  },

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

  computed: {
    isCreate() {
      return this.$route && this.$route.name && this.$route.name === 'create'
    },
  },

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
  }
}
</script>
