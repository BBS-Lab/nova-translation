<template>
  <a
    :href="url"
    :title="__(`Translate to ${targetLocale.label}`)"
    class="block p-3 cursor-pointer no-underline text-70 hover:bg-30"
  >
    <span class="nova-translation--flag">
      {{ targetLocale.label }}
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
