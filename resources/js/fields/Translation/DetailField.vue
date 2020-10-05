<template>
  <div class="flex border-b border-40">
    <div class="w-1/4 py-4">
      <h4 class="font-normal text-80">{{ trans('Language') }}</h4>
    </div>
    <div class="w-3/4 py-4 break-words flex">
      <p class="text-90 font-bold">
        {{ field.locales[field.value.locale_id].label }}
      </p>
      <div class="flex items-center ml-auto">
        <dropdown class="ml-auto h-6 flex items-center dropdown-right">
          <dropdown-trigger class="h-6 flex items-center text-xs bg-40 px-2 rounded active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline">
            <span class="text-90">{{ trans('Translations') }}</span>
          </dropdown-trigger>

          <dropdown-menu slot="menu" width="200" direction="rtl">
            <ul class="list-reset">
              <li
                v-for="locale in otherLocales"
                :key="`locale_${locale.id}`"
              >
                <router-link
                  v-if="isTranslated[locale.id]"
                  class="block p-3 cursor-pointer no-underline text-90 hover:bg-30"
                  :to="{
                name: 'detail',
                params: {
                  resourceName,
                  resourceId: translations[locale.id].translatable_id,
                },
              }"
                  :title="__(`View in ${locale.label}`)"
                >
              <span class="nova-translation--flag">
                {{ locale.label }}
              </span>
                </router-link>
                <create-translation-link
                  v-else
                  :resource-name="resourceName"
                  :resource-id="resourceId"
                  :target-locale="locale"
                />
              </li>
            </ul>
          </dropdown-menu>
        </dropdown>
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
