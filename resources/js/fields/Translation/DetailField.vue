<template>
  <div class="border-b border-40">
    <div class="py-2 flex items-center justify-between" v-if="field.value">
      <div>
        <span class="text-4xl" v-html="trans(`Flag ${field.locales[field.value.locale_id].iso.toUpperCase()}`)"/>
      </div>
      <div class="flex items-center text-right">
        <div
          class="inline-block ml-2"
          v-for="translation in field.translations" :key="`translation_${translation.locale_id}`"
          v-html="viewLink(translation)" v-if="translation.locale_id !== field.value.locale_id"
        />
      </div>
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
        return `<a class="no-underline" href="/resources/${this.resourceName}/${translation.translatable_id}"><span class="nova-translation--flag">${this.flag(this.field.locales[translation.locale_id])}</span></a>`
      },
    },
  }
</script>
