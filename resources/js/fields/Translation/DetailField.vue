<template>
  <div class="nova-translation">
    <div class="flex flex-col md:flex-row -mx-6 px-6 py-2 md:py-0 space-y-2 md:space-y-0">
      <div class="md:w-1/4 md:py-3 flex items-center">
        <h4 class="font-normal text-80">{{ trans('Language') }}</h4>
      </div>
      <div class="md:w-3/4 md:py-3 break-all lg:break-words flex items-center">
        <p class="text-90 font-bold">
          {{ field.locales[field.value.locale_id].label }}
        </p>
        <div class="flex items-center ml-auto">
          <Dropdown placement="bottom-end">
            <DropdownTrigger
                :show-arrow="true"
                class="hover:bg-gray-100 dark:hover:bg-gray-700 h-10 focus:outline-none focus:ring rounded-lg flex items-center text-sm font-semibold text-gray-600 dark:text-gray-400 px-3"
                role="navigation"
            >
              <span class="text-90">{{ trans('Translations') }}</span>
            </DropdownTrigger>
            <template #menu>
              <DropdownMenu>
                <div class="flex flex-col py-1">
                  <template v-for="locale in otherLocales" :key="`locale_${locale.id}`">
                    <template v-if="isTranslated[locale?.id] ?? false">
                      <DropdownMenuItem
                          as="link"
                          method="GET"
                          class="flex hover:bg-gray-100 py-1"
                          :href="translatedDetailRoute(locale)"
                      >
                        <Icon :solid="true" type="check-circle" class="text-green-500" />
                        <span class="ml-2">{{ locale.label }}</span>
                      </DropdownMenuItem>
                    </template>
                    <template v-else>
                      <DropdownMenuItem
                          as="link"
                          method="GET"
                          class="flex text-gray-400 hover:bg-gray-100 py-1"
                          :href="createTranslationRoute(locale)"
                      >
                        <Icon :solid="true" type="x-circle" class="text-red-500" />
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

  methods: {
    translatedDetailRoute(locale) {
      return `${Nova.config('base')}/resources/${this.resourceName}/${this.translations[locale.id].translatable_id}`
    },
    createTranslationRoute(locale) {
      return `/nova-vendor/nova-translation/translate/${this.resourceName}/${this.resourceId}/locale-${locale.id}`
    },
  }
}
</script>
