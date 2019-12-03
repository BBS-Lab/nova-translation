<template>
  <panel-item :field="field">
    <template slot="value">
      <div v-if="field.value">
        {{ trans(`Flag ${field.locales[field.value.locale_id].iso.toUpperCase()}`) }}
      </div>
    </template>
  </panel-item>
</template>

<script>
  import I18n from '../../mixins/I18n'

  export default {
    mixins: [
      I18n,
    ],

    props: [
      'field',
      'resource',
      'resourceId',
      'resourceName',
    ],
  }
</script>
