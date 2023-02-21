<template>
  <div class="nova-translation">
    <div :class="`text-${field.textAlign}`" class="flex space-x-1 divide-x" v-if="field.value">
      <template
          v-for="(otherLocale) in otherLocales"
          :key="`translation_${otherLocale?.id}`"
      >
        <div v-if="isTranslated[otherLocale?.id] ?? false" class="pl-1 first:pl-0">
          <Link
              :href="translatedDetailRoute(otherLocale)"
              class="link-default"
              @click.stop
          >
            <span>{{ otherLocale.iso }}</span>
          </Link>
        </div>
      </template>
      <span v-if="!hasTranslation">—</span>
    </div>
    <div :class="`text-${field.textAlign}`" v-else>—</div>
  </div>
</template>

<script>
import TranslationMixin from '@/mixins/Translation'

export default {
  mixins: [
    TranslationMixin,
  ],

  props: ['resourceName', 'field'],

  methods: {
    translatedDetailRoute(locale) {
      return `${Nova.config('base')}/resources/${this.resourceName}/${this.translations[locale.id].translatable_id}`.replace('//', '/')
    }
  },

}
</script>
