<template>
  <default-field :field="field" :errors="errors" v-if="field.value">
    <div>
      {{ trans(`Flag ${field.locales[field.value.locale_id].iso.toUpperCase()}`) }}
    </div>
  </default-field>
</template>

<script>
  import I18n from '../../mixins/I18n'
  import { FormField, HandlesValidationErrors } from 'laravel-nova'

  export default {
    mixins: [
      I18n,
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
    },
  }
</script>
