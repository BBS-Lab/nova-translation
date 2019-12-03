<template>
  <div class="w-full h-auto flex items-center form-control form-input form-input-bordered p-0">
    <input type="text" class="flex-1 outline-none form-control form-input shadow-none px-3" :value="url"/>
    <button type="button" class="btn btn-default btn-primary" @click.prevent="openMediaLibrary()">
      {{ trans('Choose image') }}
    </button>
  </div>
</template>

<script>
  import I18n from '../../mixins/I18n'

  export default {
    mixins: [
      I18n,
    ],

    props: {
      localeKey: {
        type: String,
        required: true,
      },
      localeId: {
        type: Number,
        required: true,
      },
      widget: {
        type: Object,
        required: true,
      },
      url: {
        type: String,
        default: '',
      },
    },

    data() {
      return {
        //
      }
    },

    methods: {
      openMediaLibrary() {
        this.$emit('edit', { localeId: this.localeId, localeKey: this.localeKey })
        this.widget.show()
      },
    },
  }
</script>
