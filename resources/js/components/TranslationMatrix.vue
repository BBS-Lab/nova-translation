<template>
  <div>
    <heading>{{ trans('Translations Matrix') }}</heading>

    <loading-view v-if="loading"/>

    <div v-else>
      <div class="mb-6 text-right">
        <button class="btn btn-link dim cursor-pointer text-80" @click.prevent="promptKeyModalOpened = true">{{ trans('Add key') }}</button>
        <button class="ml-3 btn btn-default btn-primary text-white cursor-pointer text-80" @click="saveLabels">Save</button>
      </div>

      <card>
        <table class="translation-matrix my-4 table w-full" cellpadding="0" cellspacing="0">
          <thead>
            <tr class="p-3">
              <th></th>
              <th v-for="locale in locales" :key="locale.id">{{ locale.label }}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr class="p-3" v-for="(keyI18n, key) in matrix" :key="key" :id="`tr__${key}`">
              <td>{{ key }}</td>
              <td v-for="locale in locales" :key="`${key}__${locale.id}`">
                <div v-if="(keyI18n[locale.id] && (keyI18n[locale.id].type === 'text'))">
                  <textarea class="w-full h-auto form-control form-input form-input-bordered py-3" rows="1" @input="updateLabel(key, locale.id, $event.target.value)" :id="`textarea__${key}__${locale.id}`" v-if="keyI18n[locale.id]" v-html="keyI18n[locale.id].value"/>
                </div>
                <div v-if="(keyI18n[locale.id] && (keyI18n[locale.id].type === 'upload'))">
                  <cloudinary-upload :widget="cloudinaryWidget" :locale-key="key" :locale-id="locale.id" :url="keyI18n[locale.id].value" :id="`upload__${key}__${locale.id}`" @edit="setCurrentEdit($event)"/>
                </div>
              </td>
              <td class="table-actions">
                <button class="block" @click="deleteKey(key)">
                  <icon type="delete" width="12" height="12" view-box="0 0 24 24"/>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </card>

      <div class="mt-6 text-right">
        <button class="btn btn-link dim cursor-pointer text-80" @click.prevent="promptKeyModalOpened = true">{{ trans('Add key') }}</button>
        <button class="ml-3 btn btn-default btn-primary text-white cursor-pointer text-80" @click="saveLabels">Save</button>
      </div>
    </div>

    <portal to="modals" transition="fade-transition">
      <prompt-key-modal v-if="promptKeyModalOpened" @confirm="addKey" @close="promptKeyModalOpened = false"/>
    </portal>
  </div>
</template>

<script>
  import I18n from '../mixins/I18n'
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

        return matrix
      },
    },
  }
</script>

<style scoped>
  .modal {
    background-color: rgba(0, 0, 0, 0.6);
  }

  .table tbody tr td.table-actions {
    min-width: 0;
    padding-left: 0.5em;
    padding-right: 0.5em;
    text-align: right;
    width: 1%;
    white-space: nowrap;
  }
</style>
