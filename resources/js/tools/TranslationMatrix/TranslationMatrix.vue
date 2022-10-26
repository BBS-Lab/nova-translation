<template>
  <div>
    <heading>{{ trans('Translations Matrix') }}</heading>

    <loading-view v-if="loading"/>

    <div v-else>
      <div class="mb-6 text-right">
        <button class="btn btn-link dim cursor-pointer text-80" @click.prevent="promptKeyModalOpened = true">{{ trans('Add key') }}</button>
        <button class="ml-3 btn btn-default btn-primary text-white cursor-pointer text-80" @click="saveLabels">{{ trans('Save') }}</button>
      </div>

      <card>
        <div class="rounded overflow-hidden overflow-x-scroll overflow-y-scroll relative">
          <div class="translation-matrix">
            <table class="table relative w-full border-separate">
              <thead>
              <tr>
                <th class="text-center sticky top-0 left-0 border-r z-20">{{ trans('Label') }}</th>
                <th v-for="locale in locales" :key="locale.id" class="text-center sticky top-0 border-b">
                  {{ locale.label }} ({{locale.iso}})
                </th>
                <th class="sticky top-0 border-b">&nbsp;</th>
              </tr>
              </thead>
              <tbody>
              <tr class="p-3" v-for="(keyI18n, key) in labels" :key="key" :id="`tr__${key}`">
                <th class="sticky left-0 border-r z-10 no-uppercase">{{ key }}</th>
                <td v-for="locale in locales" :key="`${key}__${locale.id}`">
                  <div v-if="(keyI18n[locale.id] && (keyI18n[locale.id].type === 'text'))" class="py-1">
                    <textarea
                      class="w-full h-auto form-control form-input form-input-bordered py-2"
                      rows="2"
                      cols="3"
                      @input="updateLabel(key, locale.id, $event.target.value)"
                      :id="`textarea__${key}__${locale.id}`" v-if="keyI18n[locale.id]"
                      v-html="keyI18n[locale.id].value"
                    />
                  </div>
                </td>
                <td class="td-fit text-right pr-6 align-middle">
                  <button
                    class="inline-flex appearance-none cursor-pointer text-70 hover:text-primary mr-3"
                    v-tooltip.click="trans('Delete')"
                    @click.prevent="deleteKey(key)"
                  >
                    <icon />
                  </button>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </card>
    </div>

    <portal to="modals" transition="fade-transition">
      <prompt-key-modal v-if="promptKeyModalOpened" @confirm="addKey" @close="promptKeyModalOpened = false"/>
    </portal>
  </div>
</template>

<script>
  import I18n from '../../mixins/I18n'

  export default {
    mixins: [
      I18n,
    ],

    components: {
      PromptKeyModal: require('./PromptKeyModal.vue').default,
    },

    data: () => ({
      labels: [],
      locales: [],
      loading: true,
      currentEdit: {},
      promptKeyModalOpened: false,
    }),

    mounted() {
      this.hydrate()
    },

    methods: {
      hydrate() {
        Nova.request().get('/nova-vendor/nova-translation/translation-matrix').then((response) => {
          console.log(response.data)
          this.labels = response.data.labels
          this.locales = response.data.locales
          this.loading = false
        }).catch((error) => {
          console.error(error)
          this.loading = false
        })
      },

      updateLabel(key, localeId, value) {
        this.labels[key][localeId].value = value;
      },

      saveLabels() {
        this.loading = true

        Nova.request().post('/nova-vendor/nova-translation/translation-matrix', { labels: this.labels }).then((response) => {
          this.labels = response.data.labels
          this.$toasted.show(this.trans('The translations have been successfully saved!'), { type: 'success' })
        }).catch((error) => {
          this.$toasted.show(this.trans('An error occurred while saving the translations!'), { type: 'error' })
        }).finally(() => {
          this.loading = false
        })
      },

      // ------------------------------------------------------------------------------

      setCurrentEdit(currentEdit) {
        this.currentEdit = currentEdit
      },

      // ------------------------------------------------------------------------------

      addKey(options) {
        this.promptKeyModalOpened = false

        if (! this.keyExists(options.key)) {
          this.addI18nKey(options.key, options.type)
        } else {
          this.$toasted.show(this.trans('The key you try to add already exists!'), { type: 'error' })
        }

        this.$nextTick(() => {
          const textarea = document.querySelector(`#textarea__${options.key}__${this.locales[0].id}`)
          if (textarea) {
            textarea.focus()
          }
        })
      },

      keyExists(key) {
        return key in this.labels
      },

      addI18nKey(key, type) {
        this.labels[key] = {}

        for (let i = 0 ; i < this.locales.length ; i++) {
          this.labels[key][this.locales[i].id] = {
            key: key,
            type: type,
            value: '',
            locale_id: this.locales[i].id
          }
        }

        this.labels =_(this.labels).toPairs().sortBy(0).fromPairs().value()
      },

      deleteKey(key) {
        delete this.labels[key]
        this.labels =_(this.labels).toPairs().sortBy(0).fromPairs().value()
      },
    },
  }
</script>

<style scoped>
  .modal {
    background-color: rgba(0, 0, 0, 0.6);
  }

  .translation-matrix {
    max-height: calc(95vh - 150px);
  }

  .top-0 {
    top: 0 !important;
  }

  .left-0 {
    left: 0 !important;
  }

  th.no-uppercase {
    text-transform: none !important;
  }

  td {
    border-top: none !important;
    border-bottom-width: 2px !important;
  }

  .table tbody tr th {
    max-width: 15rem !important;
    overflow-wrap: break-word;
  }

  .table tbody tr td:not(:last-child) {
    min-width: 20rem;
  }
</style>
