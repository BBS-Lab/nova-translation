<template>
  <div class="border-b border-40">
    <div class="py-2 flex items-center justify-between" v-if="field.value">
      <div>
        <span class="text-4xl" v-html="field.locales[field.value.locale_id].flag"/>
      </div>
      <div class="flex items-center text-right">
        <div
          class="inline-block ml-2"
          v-for="otherLocale in otherLocales"
          :key="`locale_${otherLocale.id}`"
        >
          <router-link
            v-if="isTranslated[otherLocale.id]"
            class="inline-flex cursor-pointer no-underline text-3xl"
            :to="{
                name: 'detail',
                params: {
                  resourceName,
                  resourceId: translations[otherLocale.id].translatable_id,
                },
              }"
            :title="__('View')"
          >
              <span class="nova-translation--flag">
                {{ otherLocale.flag }}
              </span>
          </router-link>
          <create-translation-link
            v-else
            :resource-name="resourceName"
            :resource-id="resourceId"
            :target-locale="otherLocale"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import I18nMixin from '../../mixins/I18n'
import TranslationMixin from '../../mixins/Translation'
import CreateTranslationLink from './CreateTranslationLink'

export default {
  components: {
    CreateTranslationLink,
  },

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
}
</script>
