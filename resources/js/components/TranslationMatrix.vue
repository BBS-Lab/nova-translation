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
        <table class="my-4 table w-full" cellpadding="0" cellspacing="0">
          <thead>
            <tr class="p-3">
              <th></th>
              <th v-for="locale in locales" :key="locale.id">{{ locale.label }}</th>
            </tr>
          </thead>
          <tbody>
            <tr class="p-3" v-for="(keyI18n, key) in matrix" :key="key" :id="'tr_' + key">
              <td>{{ key }}</td>
              <td v-for="locale in locales" :key="key + '__' + locale.id">
                <textarea class="w-full form-control form-input form-input-bordered py-3 h-auto" :id="key + '__' + locale.id" rows="1">{{ keyI18n[locale.id] ? keyI18n[locale.id] : '' }}</textarea>
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
      <PromptKeyModal v-if="promptKeyModalOpened" @confirm="addKey" @close="promptKeyModalOpened = false"/>
    </portal>
  </div>
</template>

<script>
  import Trans from '../mixins/Trans'
  import animateScrollTo from 'animated-scroll-to'

  export default {
    mixins: [
      Trans
    ],

    components: {
      PromptKeyModal: require('./PromptKeyModal.vue')
    },

    data() {
      return {
        labels: [],
        locales: [],
        loading: true,
        promptKeyModalOpened: false
      }
    },

    mounted() {
      console.log('Nova Translation Matrix mounted!')
      this.hydrate()
    },

    methods: {
      hydrate() {
        Nova.request().get('/nova-vendor/nova-translation/labels').then((response) => {
          this.labels = response.data.labels
          this.locales = response.data.locales
          this.loading = false
        }).catch((error) => {
          console.error(error)
        })
      },

      addKey(key) {
        this.promptKeyModalOpened = false

        if (! this.keyExists(key)) {
          this.addI18nKey(key)
        } else {
          this.$toasted.show(this.trans('The key you try to add already exists!'), { type: 'error' })
          animateScrollTo(document.querySelector(`#tr_${key}`), {
            speed: 500
          })
        }
      },

      keyExists(key) {
        for (let i = 0 ; i < this.labels.length ; i++) {
          if (this.labels[i].key === key) {
            return true
          }
        }

        return false
      },

      addI18nKey(key) {
        for (let i = 0 ; i < this.locales.length ; i++) {
          this.labels.push({
            id: null,
            key: key,
            value: '',
            locale_id: this.locales[i].id
          })
        }
      },

      saveLabels() {
        Nova.request().post('/nova-vendor/nova-translation/labels', { labels: this.labels }).then((response) => {
          this.$toasted.show(this.trans('The translations have been successfully saved!'), { type: 'success' })
        }).catch((error) => {
          this.$toasted.show(this.trans('An error occurred while saving the translations!'), { type: 'error' })
        })
      },

      setError(error) {
        this.errors[error] = true

        setTimeout(() => {
          this.errors[error] = false
        }, 5000)
      }
    },

    computed: {
      matrix() {
        let matrix = {}

        for (let i = 0, label ; i < this.labels.length ; i++) {
          label = this.labels[i]
          if (typeof matrix[label.key] === 'undefined') {
            matrix[label.key] = {}
          }
          matrix[label.key][label.locale_id] = label.value
        }

        return matrix
      }
    }
  }
</script>

<style>
  .modal {
    background-color: rgba(0, 0, 0, 0.6);
  }

  .table tfoot tr:hover td {
    background-color: transparent;
  }
</style>
