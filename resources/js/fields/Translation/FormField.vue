<template>
  <div class="nova-translation">
    <div class="px-6 md:px-8 mt-2 md:mt-0 w-full py-2">
      <heading level="3" v-if="isCreate">{{ locale.label }}</heading>
      <heading level="3" v-else-if="field.value">{{ field.locales[field.value.locale_id].label }}</heading>
    </div>
  </div>
</template>

<script>
import TranslationMixin from '../../mixins/Translation'
import { FormField, HandlesValidationErrors } from 'laravel-nova'

export default {
  mixins: [
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
      return this.$inertia.page.component === 'Nova.Create'
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
