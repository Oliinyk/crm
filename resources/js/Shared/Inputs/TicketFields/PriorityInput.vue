<template>
  <div>
    <label :class="{
      'text-gray font-bold min-w-100': editable,
      'form-label': !editable,
      'form-error': error
    }"
    >
      {{ $t($fieldTypes.TYPE_PRIORITY) }}:
    </label>
    <v-select
      :value="value"
      :class="{
        'flex items-center rounded cursor-pointer w-full placeholder-italic pl-2': editable,
        'w-full form-select py-0.5 flex items-center': !editable,
        'error': error,
      }"
      class="hover:bg-light"
      :options="['low', 'medium', 'high']"
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
        <div class="rounded-2xl inline-flex items-center" :class="{ 'ml-2': editable }">
          {{ $t(option.label) }}
          <icon
            :name="option.label"
            class="w-6 ml-2.5"
            :class="classes(option.label)"
          />
        </div>
      </template>
      <template slot="option" slot-scope="option">
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
     * @returns {{'fill-orange': boolean, 'fill-gray': boolean, 'fill-red': boolean}}
     */
    classes(label) {
      return {
        'fill-gray': label === 'low',
        'fill-orange': label === 'medium',
        'fill-red': label === 'high',
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
