<template>
  <modal role="dialog" @modal-close="handleClose">
    <form autocomplete="off" @keydown="handleKeydown" @submit.prevent.stop="handleConfirm" class="bg-white rounded-lg shadow-lg overflow-hidden w-action-fields">
      <div>
        <heading :level="2" class="border-b border-40 py-8 px-8">{{ trans('Add a translation key') }}</heading>

        <div class="m-8">
          <div class="action">
            <input type="text" class="w-full form-control form-input form-input-bordered" v-model="newKey">
          </div>
        </div>
      </div>

      <div class="bg-30 px-6 py-3 flex">
        <div class="flex items-center ml-auto">
          <button type="button" @click.prevent="handleClose" class="btn btn-link dim cursor-pointer text-80 ml-auto mr-6">
            {{ trans('Close') }}
          </button>
          <button type="submit" class="btn btn-default btn-primary">
            {{ trans('Confirm') }}
          </button>
        </div>
      </div>
    </form>
  </modal>
</template>

<script>
  import Trans from '../mixins/Trans'

  export default {
    mixins: [
      Trans
    ],

    data() {
      return {
        newKey: ''
      }
    },

    mounted() {
      document.querySelectorAll('.modal input')[0].focus()
    },

    methods: {
      handleKeydown(e) {
        if (['Escape', 'Enter'].indexOf(e.key) !== -1) {
          return
        }

        e.stopPropagation()
      },

      handleConfirm() {
        this.$emit('confirm', this.newKey)
      },

      handleClose() {
        this.$emit('close')
      }
    }
  }
</script>
