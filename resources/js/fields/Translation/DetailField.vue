<template>
  <PanelItem :index="index" :field="field">
    <h4 class="font-normal @sm/peekable:break-all ">
      <span>{{ trans('Language') }}</span>
    </h4>
    <template #value>
      <div class="nova-translation">
        <div class="flex flex-row-reverse justify-items-end">
          <Dropdown placement="bottom-end">
            <DropdownTrigger
              :show-arrow="true"
              class="hover:bg-gray-100 dark:hover:bg-gray-700 h-10 focus:outline-none focus:ring rounded-lg flex items-center text-sm font-semibold text-gray-600 dark:text-gray-400 px-3"
              role="navigation"
            >
              <span class="text-90">{{ trans('Translations') }}</span>
            </DropdownTrigger>
            <template #menu>
              <DropdownMenu width="auto">
                <div class="flex flex-col py-1">
                  <template v-for="locale in otherLocales" :key="`locale_${locale.id}`">
                    <template v-if="isTranslated[locale?.id] ?? false">
                      <DropdownMenuItem
                        as="link"
                        method="GET"
                        class="flex items-center hover:bg-gray-100 py-1"
                        :href="translatedDetailRoute(locale)"
                      >
                        <Icon type="solid" name="check-circle" class="text-green-500"/>
                        <span class="ml-2">{{ locale.label }}</span>
                      </DropdownMenuItem>
                    </template>
                    <template v-else>
                      <DropdownMenuItem
                        as="link"
                        method="GET"
                        class="flex items-center text-gray-400 hover:bg-gray-100 py-1"
                        :href="createTranslationRoute(locale)"
                      >
                        <Icon type="solid" name="x-circle" class="text-red-500"/>
                        <span class="ml-2">{{ locale.label }}</span>
                      </DropdownMenuItem>
                    </template>
                  </template>
                </div>
              </DropdownMenu>
            </template>
          </Dropdown>
        </div>
      </div>
    </template>
  </PanelItem>
</template>

<script>
import I18nMixin from '../../mixins/I18n'
import TranslationMixin from '../../mixins/Translation'
import CreateTranslationLink from './CreateTranslationLink'
import {Icon, PanelItem} from 'laravel-nova-ui'

export default {
  components: {
    CreateTranslationLink,
    Icon,
    PanelItem,
  },

  mixins: [
    I18nMixin,
    TranslationMixin,
  ],

  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

  methods: {
    translatedDetailRoute(locale) {
      return `${Nova.config('base')}/resources/${this.resourceName}/${this.translations[locale.id].translatable_id}`.replace('//', '/')
    },
    createTranslationRoute(locale) {
      return `/nova-vendor/nova-translation/translate/${this.resourceName}/${this.resourceId}/locale-${locale.id}`
    },
  }
}
</script>
