<template>
  <div>
    <heading>{{ trans('Translations Matrix') }}</heading>
    <loading-view v-if="loading"/>
    <div v-else>
      <div class="mt-6 text-right">
        <button class="btn btn-default btn-primary text-white cursor-pointer text-80" @click="saveLabels">Save</button>
      </div>

      <table class="mt-6 table w-full" cellpadding="0" cellspacing="0">
        <thead>
          <tr>
            <th></th>
            <th v-for="locale in locales" :key="locale.id">{{ locale.label }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="{ labels, key } in matrix" :key="key">
            <td>{{ key }}</td>
            <td v-for="locale in locales" :key="locale.id + '__' + key">
              <textarea cols="10" rows="2">{{ labels[locale.id] ? labels[locale.id] : '' }}</textarea>
            </td>
          </tr>
        </tbody>
        <tfoot class="border-t">
          <tr>
            <td class="text-center" :colspan="(1 + locales.length)">
              <a href="#" class="btn btn-link dim cursor-pointer text-80" @click.prevent="promptKeyModalOpened = true">+ Add key</a>
              <div class="mt-2 text-center alert alert-danger" v-if="errors.existingKey">The key you try to add already exists!</div>
            </td>
          </tr>
        </tfoot>
      </table>

      <div class="text-right">
        <button class="btn btn-default btn-primary text-white cursor-pointer text-80" @click="saveLabels">Save</button>
      </div>
    </div>

    <portal to="modals" transition="fade-transition">
      <PromptKeyModal v-if="promptKeyModalOpened" @confirm="addKey" @close="promptKeyModalOpened = false"/>
    </portal>
  </div>
</template>

<script>
  export default {
    components: {
      PromptKeyModal: require('./PromptKeyModal.vue')
    },

    data() {
      return {
        matrix: {},
        errors: { existingKey: false },
        locales: [],
        loading: true,
        promptKeyModalOpened: false
      }
    },

    mounted() {
      console.log('Nova Translation Matrix mounted!')
      this.loadMatrix()
    },

    methods: {
      loadMatrix() {
        Nova.request().get('/nova-vendor/nova-translation/labels').then((response) => {
          this.matrix = response.data.matrix
          this.locales = response.data.locales
          this.loading = false
        }).catch((error) => {
          console.error(error)
        })
      },

      addKey(newKey) {
        this.promptKeyModalOpened = false
console.log(`addKey "${newKey}"`)
        if (typeof this.matrix[newKey] === 'undefined') {
console.log('notExists')
          this.matrix[newKey] = {}
          for (let i = 0 ; i < this.locales.length ; i++) {
console.log(`addForLocale ${this.locales[i].iso}`)
            this.matrix[newKey][this.locales[i].id] = ''
console.log(this.matrix)
          }
        } else {
          this.errors.existingKey = true
          this.setTimeout(() => {
            this.errors.existingKey = false
          }, 5000)
        }
      },

      saveLabels() {
        console.log('Save labels')
      },

      trans(key, replace) {
        return window.config.translations[`nova-translation::${key}`]
          ? this.__(`nova-translation::${key}`, replace)
          : key
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
