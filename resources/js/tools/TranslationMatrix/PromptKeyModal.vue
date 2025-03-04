<template>
  <Modal
    data-testid="preview-resource-modal"
    :show="show"
    @close-via-escape="handleClose"
    role="alertdialog"
    size="2xl"
  >
    <form
      ref="theForm"
      autocomplete="off"
      @submit.prevent.stop="handleConfirm"
      class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden space-y-6"
    >
      <div class="space-y-6">
        <ModalHeader v-text="trans('Add a translation key', {})"/>

        <div class="action">
          <div class="flex flex-col md:flex-row">
            <div class="px-6 md:px-8 mt-2 md:mt-0 w-full md:w-1/5 md:py-5">
              <label for="newKey-default-text-field" class="inline-block pt-2 leading-tight">Key <span
                class="text-red-500 text-sm">*</span></label>
            </div>
            <div class="mt-1 md:mt-0 pb-5 px-6 md:px-8 md:w-3/5 w-full md:py-5">
              <div class="space-y-1">
                <input
                  ref="input"
                  type="text"
                  placeholder="Key"
                  class="w-full form-control form-input form-control-bordered"
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
          <Button
            variant="link"
            state="mellow"
            @click.prevent="handleClose"
            class="mr-3"
          >
            {{ trans('Cancel', {}) }}
          </Button>

          <Button
            type="submit"
            :loading="false"
            variant="solid"
            state="default"
          >
            {{ trans('Confirm', {}) }}
          </Button>
        </div>
      </ModalFooter>
    </form>
  </Modal>

</template>

<script setup>
import {useLocalization} from '@/hooks'
import {onMounted, ref} from 'vue'
import {Button} from 'laravel-nova-ui'

const emit = defineEmits()
const {trans} = useLocalization()
const newKey = ref('')
const newType = ref('text')
const input = ref(null)

defineProps([
  'show'
])

onMounted(() => input.value.focus())

const handleKeydown = (e) => {
  if (['Escape', 'Enter'].indexOf(e.key) !== -1) {
    return
  }

  e.stopPropagation()
}

const handleConfirm = () => {
  if (newKey.value.trim() !== '') {
    emit('confirm', {type: 'text', key: newKey.value})
  }
}

const handleClose = () => {
  emit('close')
}

</script>
