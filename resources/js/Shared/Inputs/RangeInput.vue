<template>
  <div>
    <label v-if="label && !selected"
           class="form-label"
           :class="{ 'label-error': errorMessage }" :for="id"
    >
      {{ label }}:
    </label>
    <div class="flex">
      <input :value="start" class="w-1/12 text-center border-b" @change="change([$event.target.value, end])" />
      <div class="w-10/12 px-4">
        <vue-slider
          :value="[start, end]"
          :lazy="true"
          :enable-cross="false"
          :min="min"
          :max="max"
          :height="1"
          :dot-size="10"
          @error="setErrors"
          @change="change"
        />
      </div>
      <input :value="end" class="w-1/12 text-center border-b" @change="change([start, $event.target.value])" />
    </div>
    <div class="flex">
      <p class="mt-2 text-sm text-red">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script>
import VueSlider from 'vue-slider-component'
import 'vue-slider-component/theme/antd.css'

const ERROR_TYPE = {
  VALUE: 1,
  INTERVAL: 2,
  MIN: 3,
  MAX: 4,
  ORDER: 5,
}
export default {
  components: {
    VueSlider,
  },
  props: {
    id: {
      type: String,
      default() {
        return `range-input-${this._uid}`
      },
    },
    label: String,
    error: String,
    selected: Boolean,
    value: {
      type: Object,
      default() {
        return {start: 0, end: 100}
      },
    },
  },
  data() {
    return {
      min: 0,
      max: 100,
      vueSliderError: '',
    }
  },
  computed: {
    /**
     * Get start value.
     *
     * @returns {number|*}
     */
    start() {
      if (this.value !== null) {
        return this.value.start
      }
      return this.min
    },
    /**
     * Get end value.
     *
     * @returns {number|*}
     */
    end() {
      if (this.value !== null) {
        return this.value.end
      }
      return this.max
    },
    /**
     * Get error messages.
     *
     * @returns {string|string}
     */
    errorMessage() {
      return this.error || this.vueSliderError
    },
  },
  methods: {
    /**
     * Set errors from the vue slider.
     *
     * @param type
     * @param msg
     */
    setErrors(type, msg) {
      switch (type) {
      case ERROR_TYPE.MIN:
        break
      case ERROR_TYPE.MAX:
        break
      case ERROR_TYPE.VALUE:
        break
      }
      this.vueSliderError = msg
    },
    /**
     * Change value event.
     *
     * @param value
     * @param index
     */
    change(value) {
      /**
       * Emit input event with a new data.
       */
      this.$emit('input', {start: value[0], end: value[1]})

      /**
       * Clear vue slider errors.
       */
      if (value[0] >= this.min && value[1] <= this.max) {
        this.vueSliderError = ''
      }
    },
  },
}
</script>

<style>
.vue-slider-process {
  background-color: #6E78B5;
}

.vue-slider-dot-handle {
  background-color: #6E78B5;
  border: 2px solid #6E78B5;
}
</style>