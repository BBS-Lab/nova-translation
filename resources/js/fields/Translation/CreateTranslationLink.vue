<template>
  <a
    :href="url"
    :title="__('Translate')"
    class="inline-flex cursor-pointer no-underline text-3xl opacity-25"
  >
    <span class="nova-translation--flag">
      {{ targetLocale.flag }}
    </span>
  </a>
</template>

<script>
import I18nMixin from '../../mixins/I18n'
import TranslationMixin from '../../mixins/Translation'

export default {
  mixins: [
    I18nMixin,
    TranslationMixin
  ],

  props: [
    'resourceName',
    'resourceId',
    'targetLocale',
  ],

  computed: {
    url() {
      return `/nova-vendor/nova-translation/translate/${this.resourceName}/${this.resourceId}/locale-${this.targetLocale.id}`
    }
  }
}
</script>

<style scoped>

</style>
