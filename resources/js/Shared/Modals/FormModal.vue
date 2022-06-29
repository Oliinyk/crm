<template>
  <div class="pt-12 pb-32 px-9 sm:pb-14 sm:h-full sm:overflow-auto h-screen overflow-y-scroll sm:max-h-screen-90">
    <form @submit.prevent="submit" @change="$emit('change')">
      <!--header-->
      <div class="flex items-start items-center justify-between pb-8">
        <h3 class="text-lg text-dark font-semibold">
          {{ name }}
        </h3>
        <button class="btn-icon btn-close" @click="$emit('close')">
          <icon name="close" class="w-6 h-6 fill-secondary"></icon>
        </button>
      </div>
      <!--body-->
      <div class="relative flex-auto">
        <div class="flex flex-wrap">
          <slot />
        </div>
      </div>
      <!--footer-->
      <div v-if="showButtons" class="flex rounded-b"
           :class="showDeleteButton || showResetButton ? 'justify-between':'justify-end'"
      >
        <button v-if="showDeleteButton"
                class="btn-outline-danger inline-flex mr-3.5 group"
                type="button"
                @click="$emit('delete')"
        >
          <icon name="trash" class="w-6 h-6 mr-2.5 fill-red group-hover:fill-white"></icon>
          {{ $t('Delete') }}
        </button>
        <button v-if="showResetButton" class="btn-outline-danger inline-flex mr-3.5 group" type="button"
                @click="$emit('reset')"
        >
          {{ resetButtonName === '' ? $t('Reset') : resetButtonName }}
        </button>
        <div class="flex">
          <button v-if="showCancelButton"
                  class="btn-outline mr-3.5 btn-cancel"
                  type="button"
                  @click="$emit('close')"
          >
            {{ $t('Cancel') }}
          </button>
          <loading-button v-if="showSubmitButton"
                          :loading="form.processing"
                          :class="showResetButton ? 'btn-accent':'btn-cta-primary'"
                          type="submit"
          >
            {{ submitButtonName === '' ? $t('Create') : submitButtonName }}
          </loading-button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import LoadingButton from '@/Shared/LoadingButton'

export default {
  name: 'LargeModal',
  components: {
    LoadingButton,
  },
  props: {
    form: Object,
    name: String,
    showButtons: {
      type: Boolean,
      default: true,
    },
    showSubmitButton: {
      type: Boolean,
      default: true,
    },
    showResetButton: {
      type: Boolean,
      default: false,
    },
    showDeleteButton: {
      type: Boolean,
      default: false,
    },
    showCancelButton: {
      type: Boolean,
      default: true,
    },
    submitButtonName: {
      type: String,
      default: '',
    },
    resetButtonName: {
      type: String,
      default: '',
    },
  },
  methods: {
    submit() {
      this.$emit('submit')
    },
  },
}
</script>
