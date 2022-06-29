<template>
  <div>
    <label v-if="label" class="form-label" :for="id">{{ label }}:</label>
    <div class="w-full">
      <textarea :id="id"
                ref="input"
                v-bind="$attrs"
                v-model="currentValue"
                class="form-textarea flex"
                :class="[
                  { error: error },
                  { 'mx-0': editable && focused },
                  { '-mx-1.5': editable && !focused },
                  { 'border-0 py-1 px-1.5 placeholder-italic': editable }
                ]"
                :style="inputStyle"
                @input="saveInput"
                @focus="focus()"
      />
      <div v-if="buttonsControl && focused" class="flex align-center mt-1">
        <button class="btn-cta-primary inline-flex mr-4 font-semibold" @click="saveEdit()">Save</button>
        <button @click="cancelEdit()"><icon name="close" class="w-6 h-6 fill-gray"></icon></button>
      </div>
      <div v-if="error" class="form-error">{{ error }}</div>
    </div>
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    id: {
      type: String,
      default() {
        return `textarea-input-${this._uid}`
      },
    },
    value: String,
    label: String,
    error: String,
    editable: Boolean,
    autosize: {
      type: Boolean,
      default: false,
    },
    buttonsControl: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      currentValue: this.value,
      inputHeight: '0',
      focused: false,
    }
  },
  computed: {
    inputStyle() {
      if(this.autosize) {
        return {
          'min-height': this.inputHeight,
        }
      }
      return null
    },
  },
  watch: {
    currentValue() {
      this.resize()
    },
    value() {
      this.currentValue = this.value
    },
  },
  mounted() {
    this.resize()
    document.addEventListener('click', this.clickOutside)
  },
  methods: {
    focus() {
      this.$refs.input.focus()
      this.focused = true
    },
    select() {
      this.$refs.input.select()
    },
    resize() {
      this.inputHeight = `${this.$refs.input.scrollHeight}px`
    },
    clickOutside(e) {
      this.focused = this.$el.contains(e.target)
    },
    cancelEdit() {
      this.currentValue = this.value
      this.focused = false
    },
    saveEdit() {
      this.focused = false
      this.$emit('input', this.currentValue)
      this.$emit('save')
    },
    saveInput(e) {
      if(!this.buttonsControl) {
        this.$emit('input', e.target.value)
      }
    },
  },
}
</script>
