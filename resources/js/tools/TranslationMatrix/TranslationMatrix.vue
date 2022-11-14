<template>
  <div class="nova-translation">
    <Heading level="1" class="mb-3">{{ trans('Translations Matrix') }}</Heading>

    <LoadingView :loading="loading">
      <div class="flex">
        <div class="w-full flex items-center mb-6"><!-- Create / Attach Button -->
          <div class="flex-shrink-0 ml-auto"><!-- Attach Related Models --><!-- Create Related Models -->
            <button
                size="md"
                class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0"
                @click.prevent="openPromptKeyModal"
            >
              <span class="hidden md:inline-block">{{ trans('Add key') }}</span>
              <span class="inline-block md:hidden">{{ trans('Add key') }}</span>
            </button>
            <button
                size="md"
                class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0 ml-3"
                @click.prevent="saveLabels"
            >
              <span class="hidden md:inline-block">{{ trans('Save') }}</span>
              <span class="inline-block md:hidden">{{ trans('Save') }}</span>
            </button>
          </div>
        </div>
      </div>

      <card>
        <div class="rounded overflow-hidden">
          <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
            <table class="table overflow-x-scroll overflow-y-scroll relative w-full relative border-separate border-spacing-0">
              <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th
                    class="bg-gray-50 dark:bg-gray-800 text-left px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2 border-r border-b border-gray-200 sticky top-0 left-0 z-30"
                >
                  {{ trans('Label') }}
                </th>
                <th
                    v-for="(locale, index) in locales"
                    :key="locale.id" class="bg-gray-50 dark:bg-gray-800  text-left px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2 border-b border-gray-200 sticky top-0"
                    :class="{
                      'border-l': index !== 0
                    }"
                >
                  {{ locale.label }} ({{locale.iso}})
                </th>
                <th class="bg-gray-50 dark:bg-gray-800 text-left px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2 border-b border-l border-gray-200 sticky top-0 z-30 right-0">
                  {{ trans('Actions') }}
                </th>
              </tr>
              </thead>
              <tbody class="">
              <tr class="p-3 border-t" v-for="(keyI18n, key) in labels" :key="key" :id="`tr__${key}`">
                <th
                    class="bg-white text-left px-2 whitespace-nowrap text-gray-500 text-xxs tracking-wide py-2 no-uppercase border-r sticky left-0 z-20"
                >
                  {{ key }}
                </th>
                <td
                    v-for="(locale, index) in locales"
                    :key="`${key}__${locale.id}`"
                    class="border-gray-200 overflow-hidden hover:bg-gray-50"
                    :class="{
                      'border-l': index !== 0
                    }"
                >
                  <div class="w-full h-full overflow-hidden focus-within:outline-3 focus-within:outline">
                    <textarea
                        class="w-full h-full focus:outline-none p-2 border-none bg-transparent"
                        @input="updateLabel(key, locale.id, $event.target.value)"
                        :id="`textarea__${key}__${locale.id}`"
                        v-html="keyI18n[locale.id]?.value"
                    />
                  </div>

                </td>
                <td class="border-l border-gray-200 align-middle text-center p-3 bg-white z-20 sticky right-0">
                  <button
                      class="inline-flex appearance-none cursor-pointer text-70 hover:text-primary"
                      v-tooltip.click="trans('Delete')"
                      @click.prevent="deleteKey(key)"
                  >
                    <Icon type="trash" />
                  </button>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </card>

      <PromptKeyModal
          v-if="promptKeyModalOpened"
          :show="promptKeyModalOpened"
          @confirm="addKey"
          @close="closePromptKeyModal"
      />
    </LoadingView>
  </div>
</template>

<script setup>
import { useLocalization } from '@/hooks'
import PromptKeyModal from '@/tools/TranslationMatrix/PromptKeyModal'
import { nextTick, onMounted, ref } from 'vue'

const { trans } = useLocalization()

const labels = ref([])
const locales = ref([])
const loading = ref(true)
const currentEdit = ref({})
const promptKeyModalOpened = ref(false)


const hydrate = () => {
  Nova.request().get('/nova-vendor/nova-translation/translation-matrix').then((response) => {
    console.log(response.data)
    labels.value = response.data.labels
    locales.value = response.data.locales
    loading.value = false
  }).catch((error) => {
    console.error(error)
    loading.value = false
  })
}

const updateLabel = (key, localeId, value) => {
  labels.value[key][localeId].value = value;
}

const saveLabels = () => {
  loading.value = true

  Nova.request().post('/nova-vendor/nova-translation/translation-matrix', { labels: labels.value }).then((response) => {
    labels.value = response.data.labels
    Nova.success(trans('The translations have been successfully saved!'))
  }).catch((error) => {
    console.error(error)
    Nova.error(trans('An error occurred while saving the translations!'))
  }).finally(() => {
    loading.value = false
  })
}

// ------------------------------------------------------------------------------

const setCurrentEdit = (currentEdit) => {
  currentEdit.value = currentEdit
}

// ------------------------------------------------------------------------------

const openPromptKeyModal = () => promptKeyModalOpened.value = true
const closePromptKeyModal = () => promptKeyModalOpened.value = false

const addKey = (options) => {
  promptKeyModalOpened.value = false

  if (! keyExists(options.key)) {
    addI18nKey(options.key, options.type)
  } else {
    Nova.error(trans('The key you try to add already exists!'))
  }

  nextTick(() => {
    const textarea = document.querySelector(`#textarea__${options.key}__${locales.value[0].id}`)
    if (textarea) {
      textarea.focus()
    }
  })
}

const keyExists = (key) => key in labels.value

const addI18nKey = (key, type) => {
  labels.value[key] = {}

  for (let i = 0 ; i < locales.value.length ; i++) {
    labels.value[key][locales.value[i].id] = {
      key: key,
      type: type,
      value: '',
      locale_id: locales.value[i].id
    }
  }

  labels.value =_(labels.value).toPairs().sortBy(0).fromPairs().value()
}

const deleteKey = (key) => {
  delete labels.value[key]
  labels.value =_(labels.value).toPairs().sortBy(0).fromPairs().value()
}

onMounted(() => hydrate())
</script>

<style scoped>
  .table tbody tr th {
    max-width: 15rem !important;
    overflow-wrap: break-word;
  }

  .table tbody tr td:not(:last-child) {
    min-width: 20rem;
  }

  .table tbody tr:not(:last-child) td, .table tbody tr:not(:last-child) th {
    border-bottom-width: 1px;
    --tw-border-opacity: 1;
    border-color: rgb(229 231 235 / var(--tw-border-opacity));
  }

</style>
