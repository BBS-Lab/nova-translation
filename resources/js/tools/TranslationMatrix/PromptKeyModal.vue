<template>
  <Modal
      data-testid="preview-resource-modal"
      :show="show"
      @close-via-escape="handleClose"
      role="alertdialog"
      size="2xl"
  >
<!--    <form autocomplete="off" @keydown="handleKeydown" @submit.prevent.stop="handleConfirm" class="bg-white rounded-lg shadow-lg overflow-hidden w-action-fields">-->
<!--      <div>-->
<!--        <heading :level="2" class="border-b border-40 py-8 px-8">{{ trans('Add a translation key') }}</heading>-->

<!--        <div class="m-8">-->
<!--          <div class="mt-2 action">-->
<!--            <input type="text" class="w-full form-control form-input form-input-bordered" v-model="newKey">-->
<!--          </div>-->
<!--        </div>-->
<!--      </div>-->

<!--      <div class="bg-30 px-6 py-3 flex">-->
<!--        <div class="flex items-center ml-auto">-->
<!--          <button type="button" @click.prevent="handleClose" class="btn btn-link dim cursor-pointer text-80 ml-auto mr-6">-->
<!--            {{ trans('Close') }}-->
<!--          </button>-->
<!--          <button type="submit" class="btn btn-default btn-primary">-->
<!--            {{ trans('Confirm') }}-->
<!--          </button>-->
<!--        </div>-->
<!--      </div>-->
<!--    </form>-->
    <form
        ref="theForm"
        autocomplete="off"
        @submit.prevent.stop="handleConfirm"
        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden space-y-6"
    >
      <div class="space-y-6">
        <ModalHeader v-text="trans('Add a translation key', {})" />

        <div class="action">
          <div class="flex flex-col md:flex-row">
            <div class="px-6 md:px-8 mt-2 md:mt-0 w-full md:w-1/5 md:py-5">
              <label for="newKey-default-text-field" class="inline-block pt-2 leading-tight">Key <span class="text-red-500 text-sm">*</span></label>
            </div>
            <div class="mt-1 md:mt-0 pb-5 px-6 md:px-8 md:w-3/5 w-full md:py-5">
              <div class="space-y-1">
                <input
                    type="text"
                    placeholder="Key"
                    class="w-full form-control form-input form-input-bordered"
                    id="newKey-default-text-field"
                    maxlength="-1"
                    v-model="newKey"
                >
              </div>
            </div>
          </div>
        </div>
      </div>

      <ModalFooter>
        <div class="flex items-center ml-auto">
          <DangerButton
              component="button"
              type="button"
              dusk="cancel-action-button"
              class="ml-auto mr-3"
              @click="handleClose"
          >
            {{ trans('Cancel', {}) }}
          </DangerButton>

          <LoadingButton
              type="submit"
              ref="runButton"
              component="DefaultButton"
          >
            {{ trans('Confirm', {}) }}
          </LoadingButton>
        </div>
      </ModalFooter>
    </form>
  </Modal>

</template>

<script setup>
import { useLocalization } from '@/hooks'
import { onMounted, ref } from 'vue'
import LoadingButton from "@/components/LoadingButton.vue";
import DangerButton from "@/components/DangerButton.vue";

const emit = defineEmits()
const { trans } = useLocalization()
const newKey = ref('')
const newType = ref('text')

defineProps([
    'show'
])
//onMounted(() => document.querySelectorAll('.modal input')[0].focus())

const handleKeydown = (e) => {
  if (['Escape', 'Enter'].indexOf(e.key) !== -1) {
    return
  }

  e.stopPropagation()
}

const handleConfirm = () => {
  if (newKey.value.trim() !== '') {
    emit('confirm', { type: 'text', key: newKey.value })
  }
}

const handleClose = () => {
  emit('close')
}

</script>
