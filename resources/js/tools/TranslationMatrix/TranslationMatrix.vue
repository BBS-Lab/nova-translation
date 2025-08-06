<template>
  <div class="nova-translation">
    <Heading level="1" class="mb-3">{{ trans('Translations Matrix') }}</Heading>

    <LoadingView :loading="loading">
      <div class="flex">
        <div class="w-full flex items-center mb-6">
          <!-- Create / Attach Button -->
          <div class="flex-shrink-0 ml-auto">
            <!-- Attach Related Models --><!-- Create Related Models -->
            <div class="flex gap-3">
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
                class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0"
                @click.prevent="saveAllLabels"
                :disabled="!hasUnsavedChanges || isSavingAll"
              >
                <Icon v-if="isSavingAll" type="loader" class="animate-spin h-4 w-4 mr-2" />
                <span class="hidden md:inline-block">{{ trans('Save All') }}</span>
                <span class="inline-block md:hidden">{{ trans('Save All') }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <card>
        <div class="rounded overflow-hidden">
          <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
            <table
              class="table overflow-x-scroll overflow-y-scroll relative w-full relative border-separate border-spacing-0 dark:border-gray-700"
            >
              <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th
                    class="bg-gray-50 dark:bg-gray-800 text-left px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2 border-r border-b border-gray-200 dark:border-gray-700 sticky top-0 left-0 z-30"
                  >
                    {{ trans('Label') }}
                  </th>
                  <th
                    v-for="(locale, index) in locales"
                    :key="locale.id"
                    class="bg-gray-50 dark:bg-gray-800 text-left px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2 border-b border-gray-200 dark:border-gray-700 sticky top-0"
                    :class="{
                      'border-l': index !== 0,
                    }"
                  >
                    {{ locale.label }} ({{ locale.iso }})
                  </th>
                  <th
                    class="bg-gray-50 dark:bg-gray-800 text-left px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2 border-b border-l border-gray-200 dark:border-gray-700 sticky top-0 z-30 right-0"
                  >
                    {{ trans('Actions') }}
                  </th>
                </tr>
              </thead>
              <tbody class="">
                <tr
                  class="p-3 border-t dark:border-gray-700"
                  v-for="(keyI18n, key) in labels"
                  :key="key"
                  :id="`tr__${key}`"
                >
                  <th
                    class="bg-white dark:bg-gray-800 text-left px-2 whitespace-nowrap text-gray-500 dark:text-gray-400 text-xxs tracking-wide py-2 no-uppercase border-r dark:border-gray-700 sticky left-0 z-20"
                  >
                    {{ key }}
                  </th>
                  <td
                    v-for="(locale, index) in locales"
                    :key="`${key}__${locale.id}`"
                    class="border-gray-200 dark:border-gray-700 overflow-hidden"
                    :class="{
                      'border-l': index !== 0,
                    }"
                  >
                    <div class="relative w-full h-full">
                      <div
                        class="w-full h-full overflow-hidden focus-within:outline-3 focus-within:outline"
                      >
                        <textarea
                          class="w-full h-full focus:outline-none p-2 pr-[100px] border-none bg-transparent"
                          @input="updateLabel(key, locale.id, $event.target.value)"
                          :id="`textarea__${key}__${locale.id}`"
                          v-html="keyI18n[locale.id]?.value"
                        />
                      </div>
                      <div class="absolute top-1 right-1" v-if="keyI18n[locale.id]?.isDirty">
                        <button
                          class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-2 h-6 text-xs"
                          @click.prevent="saveLabel(key, locale.id)"
                          :disabled="keyI18n[locale.id]?.isSaving"
                        >
                          <Icon
                            v-if="keyI18n[locale.id]?.isSaving"
                            type="loader"
                            class="animate-spin h-3 w-3 mr-1"
                          />
                          <span>{{ trans('Save') }}</span>
                        </button>
                      </div>
                    </div>
                  </td>
                  <td
                    class="border-l border-gray-200 dark:border-gray-700 dark:text-gray-400 align-middle text-center p-3 bg-white dark:bg-gray-800 z-20 sticky right-0"
                  >
                    <button
                      class="inline-flex appearance-none cursor-pointer text-70 hover:text-primary"
                      v-tooltip.click="trans('Delete')"
                      @click.prevent="deleteKey(key)"
                    >
                      <Icon name="trash" class="!w-4 !h-4" />
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
import { nextTick, onMounted, ref, computed } from 'vue'
import _ from 'lodash'
import { Icon } from 'laravel-nova-ui'

const { trans } = useLocalization()

const labels = ref([])
const locales = ref([])
const loading = ref(true)
const isSavingAll = ref(false)
const promptKeyModalOpened = ref(false)

const hasUnsavedChanges = computed(() => {
  return Object.values(labels.value).some(keyI18n =>
    Object.values(keyI18n).some(item => item.isDirty)
  )
})

const hydrate = () => {
  Nova.request()
    .get('/nova-vendor/nova-translation/translation-matrix')
    .then(response => {
      labels.value = response.data.labels
      locales.value = response.data.locales
      loading.value = false
    })
    .catch(error => {
      console.error(error)
      loading.value = false
    })
}

const updateLabel = (key, localeId, value) => {
  if (!labels.value[key][localeId].isDirty) {
    labels.value[key][localeId] = {
      ...labels.value[key][localeId],
      isDirty: true,
      isSaving: false,
    }
  }
  labels.value[key][localeId].value = value
}

const saveLabel = async (key, localeId, silent = false) => {
  const label = labels.value[key][localeId]

  if (!label || !label.isDirty) return

  label.isSaving = true

  try {
    const response = await Nova.request().post('/nova-vendor/nova-translation/translation-matrix', {
      key: key,
      type: label.type,
      value: label.value,
      locale_id: localeId,
    })

    if (response.data.label) {
      labels.value[key][localeId] = {
        ...response.data.label,
        isDirty: false,
        isSaving: false,
      }
    }

    if (!silent) {
      Nova.success(trans('Translation saved successfully!'))
    }
  } catch (error) {
    console.error(error)
    if (!silent) {
      Nova.error(trans('Failed to save translation'))
    }
    label.isSaving = false
    throw error
  }
}

const saveAllLabels = async () => {
  if (!hasUnsavedChanges.value || isSavingAll.value) return

  isSavingAll.value = true

  try {
    const translations = []
    for (const [key, keyI18n] of Object.entries(labels.value)) {
      for (const [localeId, label] of Object.entries(keyI18n)) {
        if (label.isDirty) {
          translations.push({
            key,
            type: label.type,
            value: label.value,
            locale_id: parseInt(localeId),
          })
        }
      }
    }

    const response = await Nova.request().post(
      '/nova-vendor/nova-translation/translation-matrix/save-all',
      {
        translations,
      }
    )

    if (response.data.labels) {
      response.data.labels.forEach(savedLabel => {
        if (labels.value[savedLabel.key] && labels.value[savedLabel.key][savedLabel.locale_id]) {
          labels.value[savedLabel.key][savedLabel.locale_id] = {
            ...savedLabel,
            isDirty: false,
            isSaving: false,
          }
        }
      })
    }

    if (response.data.errors && response.data.errors.length > 0) {
      Nova.error(trans('Some translations failed to save'))
      console.error('Save errors:', response.data.errors)
    } else {
      Nova.success(trans('All translations saved successfully!'))
    }
  } catch (error) {
    console.error(error)
    Nova.error(trans('Failed to save translations'))
  } finally {
    isSavingAll.value = false
  }
}

const openPromptKeyModal = () => (promptKeyModalOpened.value = true)
const closePromptKeyModal = () => (promptKeyModalOpened.value = false)

const addKey = options => {
  promptKeyModalOpened.value = false

  if (!keyExists(options.key)) {
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

const keyExists = key => key in labels.value

const addI18nKey = (key, type) => {
  labels.value[key] = {}

  for (let i = 0; i < locales.value.length; i++) {
    labels.value[key][locales.value[i].id] = {
      key: key,
      type: type,
      value: '',
      locale_id: locales.value[i].id,
      isDirty: false,
      isSaving: false,
    }
  }

  labels.value = _(labels.value).toPairs().sortBy(0).fromPairs().value()
}

const deleteKey = async key => {
  if (!confirm(trans('Are you sure you want to delete this translation?'))) {
    return
  }

  try {
    loading.value = true

    await Nova.request().post(
      `/nova-vendor/nova-translation/translation-matrix/delete/${encodeURI(key)}`
    )

    delete labels.value[key]
    labels.value = _(labels.value).toPairs().sortBy(0).fromPairs().value()

    Nova.success(trans('Translation deleted successfully!'))
  } catch (error) {
    console.error(error)
    Nova.error(trans('Failed to delete translation'))
    await hydrate()
  } finally {
    loading.value = false
  }
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

.table tbody tr:not(:last-child) td,
.table tbody tr:not(:last-child) th {
  border-bottom-width: 1px;
  --tw-border-opacity: 1;
}
</style>
