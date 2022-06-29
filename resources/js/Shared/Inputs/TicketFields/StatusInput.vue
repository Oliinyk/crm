<template>
  <div>
    <label
      v-if="name"
      :class="{
        'text-gray font-bold min-w-100': editable,
        'form-label': !editable,
        'form-error': error
      }"
    >
      {{ $t($fieldTypes.TYPE_STATUS) }}:
    </label>
    <v-select
      :value="value"
      :class="{
        'flex items-center rounded cursor-pointer w-full placeholder-italic pl-2': editable,
        'w-full form-select py-0.5 flex items-center': !editable,
        'error': error,
      }"
      class="hover:bg-light"
      :options="['open', 'in_progress', 'resolved']"
      :placeholder="placeholder"
      @input="input"
    >
      <!-- search input -->
      <template #search="{ attributes, events }">
        <input
          v-model="searchString"
          class="vs__search"
          v-bind="attributes"
          v-on="events"
        />
      </template>
      <!-- end search input -->
      <template #selected-option-container="{ option }">
        <div class="rounded-2xl inline-flex items-center" :class="[classes(option.label), { 'ml-2': editable }]">
          {{ $t(option.label) }}
        </div>
      </template>
      <template #option="option">
        {{ $t(option.label) }}
      </template>
    </v-select>
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script>

export default {
  props: {
    value: [String, Number],
    error: String,
    placeholder: {
      type: String,
      default: '',
    },
    editable: Boolean,
    name: String,
  },
  data() {
    return {
      searchString: null,
    }
  },
  methods: {
    /**
     * Get the computed classes for the component.
     * @param label
     * @returns {{'bg-primary-opacity text-primary-dark': boolean, 'px-3 py-0.5': boolean, 'bg-orange-opacity text-orange-dark': boolean, 'bg-red-opacity text-red-dark': boolean}}
     */
    classes(label) {
      return {
        'px-3 py-0.5': true,
        'bg-red-opacity text-red-dark': label === 'open',
        'bg-orange-opacity text-orange-dark': label === 'in_progress',
        'bg-primary-opacity text-primary-dark': label === 'resolved',
      }
    },
    input(input) {
      /**
       * Set current value
       */
      this.$emit('input', input)
      /**
       * Clear search stirng
       * @type {null}
       */
      this.searchString = null
    },
  },
}
</script>
