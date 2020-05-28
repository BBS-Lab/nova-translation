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
        <div class="overflow-hidden overflow-x-scroll overflow-y-auto relative">
          <div class="translation-matrix">
            <table class="table w-full">
              <thead>
              <tr>
                <th class="text-center sticky pin-l pin-t border-rb z-10">{{ trans('Label') }}</th>
                <th v-for="locale in locales" :key="locale.id" class="text-center sticky pin-t border-b">
                  {{ locale.label }}
                </th>
                <th class="sticky pin-t border-b">&nbsp;</th>
              </tr>
              </thead>
              <tbody>
              <tr class="p-3" v-for="(keyI18n, key) in matrix" :key="key" :id="`tr__${key}`">
                <th class="sticky pin-l border-rb">{{ key }}</th>
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
                  <div v-if="(keyI18n[locale.id] && (keyI18n[locale.id].type === 'upload'))">
                    <cloudinary-upload :widget="cloudinaryWidget" :locale-key="key" :locale-id="locale.id" :url="keyI18n[locale.id].value" :id="`upload__${key}__${locale.id}`" @edit="setCurrentEdit($event)"/>
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

      <div class="mt-6 text-right">
        <button class="btn btn-link dim cursor-pointer text-80" @click.prevent="promptKeyModalOpened = true">{{ trans('Add key') }}</button>
        <button class="ml-3 btn btn-default btn-primary text-white cursor-pointer text-80" @click="saveLabels">{{ trans('Save') }}</button>
      </div>
    </div>

    <portal to="modals" transition="fade-transition">
      <prompt-key-modal v-if="promptKeyModalOpened" @confirm="addKey" @close="promptKeyModalOpened = false"/>
    </portal>
  </div>
</template>

<script>
  import I18n from '../../mixins/I18n'
  import animateScrollTo from 'animated-scroll-to'

  export default {
    mixins: [
      I18n,
    ],

    components: {
      PromptKeyModal: require('./PromptKeyModal.vue'),
      CloudinaryUpload: require('./CloudinaryUpload.vue'),
    },

    data() {
      return {
        labels: [],
        locales: [],
        loading: true,
        currentEdit: {},
        cloudinaryWidget: null,
        promptKeyModalOpened: false,
      }
    },

    mounted() {
      this.hydrate()
    },

    methods: {
      hydrate() {
        Nova.request().get('/nova-vendor/nova-translation/translation-matrix').then((response) => {
          this.setupCloudinaryWidget(response.data.cloudinary)
          this.labels = response.data.labels
          this.locales = response.data.locales
          this.loading = false
        }).catch((error) => {
          console.error(error)
        })
      },

      updateLabel(key, localeId, value) {
        for (let i = 0 ; i < this.labels.length ; i++) {
          if ((this.labels[i].key === key) && (this.labels[i].locale_id === localeId)) {
            this.labels[i].value = value
            break
          }
        }
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

      setupCloudinaryWidget(meta) {
        this.cloudinaryWidget = cloudinary.createMediaLibrary({
          api_key: meta.api_key,
          multiple: false,
          username: meta.username,
          signature: meta.signature,
          timestamp: meta.timestamp,
          cloud_name: meta.cloud_name,
        }, {
          insertHandler: (data) => {
            if (data.assets.length > 0) {
              this.updateLabel(this.currentEdit.localeKey, this.currentEdit.localeId, data.assets[0].public_id)
            }
          },
        })
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
          animateScrollTo(document.querySelector(`#tr__${options.key}`), {
            speed: 500
          })
        })
      },

      keyExists(key) {
        for (let i = 0 ; i < this.labels.length ; i++) {
          if (this.labels[i].key === key) {
            return true
          }
        }

        return false
      },

      addI18nKey(key, type) {
        for (let i = 0 ; i < this.locales.length ; i++) {
          this.labels.push({
            key: key,
            type: type,
            value: '',
            locale_id: this.locales[i].id
          })
        }
      },

      deleteKey(key) {
        let labels = []

        for (let i = 0 ; i < this.labels.length ; i++) {
          if (this.labels[i].key !== key) {
            labels.push(this.labels[i])
          }
        }

        this.labels = labels
      },
    },

    computed: {
      matrix() {
        let matrix = {}

        for (let i = 0, label ; i < this.labels.length ; i++) {
          label = this.labels[i]
          if (typeof matrix[label.key] === 'undefined') {
            matrix[label.key] = {}
          }
          matrix[label.key][label.locale_id] = {
            type: label.type,
            value: label.value
          }
        }

        let ordered = {}
        Object.keys(matrix).sort().forEach((key) => {
          ordered[key] = matrix[key];
        });

        return ordered
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

  thead th.border-rb {
    border-bottom: none;
  }

  th.border-rb {
    border-right: none;
    box-shadow: 2px 2px 0 0 var(--50);
  }

  th.border-b {
    border-bottom: none;
    box-shadow: 0 2px 0 0 var(--50);
  }

  .table tbody tr td:not(:last-child) {
    min-width: 20rem;
  }
</style>
