<template>
  <v-select :value="currentValue"
            :placeholder="placeholder"
            :filterable="false"
            :options="searchResults"
            :multiple="multiple"
            :close-on-select="closeOnSelect"
            :class="classes"
            :url="url"
            :get-option-label="option => option.name"
            @search="fetchOptions"
            @search:focus="search"
            @search:blur="save"
            @input="input"
            @deselect="deselect"
            @option:selected="optionSelected"
  >
    <!-- search input -->
    <template #search="{ attributes, events }">
      <input v-model="searchString" class="vs__search" v-bind="attributes" v-on="events" />
    </template>
    <!-- end search input -->
    <template #no-options>
      <p class="text-gray text-xs font-bold uppercase">
        <span class="flex py-3">
          {{ $t('No matches') }}
        </span>
      </p>
    </template>
    <template #list-header>
      <list-header :search-results="searchResults" :meta="meta" :search-form="searchForm" />
    </template>
    <template #option="option">
      <div class="flex items-center">
        <user-icon v-if="userImage" :full-name="option.name" :src="option.image.url" />
        {{ option.name }}
      </div>
    </template>
    <template #selected-option="{ name, image, title }">
      <selected-option :multiple="multiple"
                       :user-image="userImage"
                       :name="name"
                       :image="image"
                       :title="title"
      />
    </template>
  </v-select>
</template>

<script>
import Vue from 'vue'
import vSelect from 'vue-select'
import Deselect from './Deselect.vue'
import SearchMixin from '@/Setup/SearchMixin'
import SelectedOption from '@/Shared/Inputs/Search/SelectedOption'
import ListHeader from '@/Shared/Inputs/Search/ListHeader'

Vue.component('VSelect', vSelect)
vSelect.props.components.default = () => ({Deselect})

export default {
  name: 'VselectSearch',
  components: {ListHeader, SelectedOption},
  mixins: [
    SearchMixin,
  ],
  props: {
    value: [Object, Array, String],
    editable: Boolean,
    relativeList: Boolean,
    multiple: {
      type: Boolean,
      default: false,
    },
    closeOnSelect: {
      type: Boolean,
      default: true,
    },
    url: String,
    placeholder: String,
    userImage: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      currentValue: this.value,
      searchResults: [],
      meta: null,
      searchString: null,
      isChanged: false,
    }
  },
  computed: {
    /**
     * Get the computed classes for the component,
     * @returns {[(string),(string),{'pl-2 pr-2': boolean}]}
     */
    classes() {
      return [
        this.editable || this.relativeList ? '' : 'form-select pl-3 pr-6',
        this.relativeList ? 'relative-list' : 'flex items-center p-0.5 hover:bg-light rounded w-full',
        {'pl-2 pr-2': this.editable},
      ]
    },
  },
  watch: {
    value(val) {
      this.currentValue = val
      console.log('value', val)
    },
  },
  methods: {
    /**
     * Save selected results.
     */
    save() {
      console.log('save')

      if (!this.isChanged) {
        return
      }

      if (this.multiple) {
        this.$emit('input', this.currentValue)
      } else {
        if (this.currentValue) {
          this.$emit('input', [this.currentValue])
        }else{
          this.$emit('input', this.currentValue)
        }
      }

      this.isChanged = false
    },

    /**
     * When the search was successful.
     *
     * @param data
     */
    onSuccessFetch({data}) {

      /**
       *  Clear search results.
       *  Update the metadata.
       */
      this.searchResults = []
      this.meta = data.meta

      /**
       * Add search results.
       */
      data.data.forEach(item => {
        if (this.multiple) {
          this.addSearchResultsToTheMultipleSelectValue(item)
        } else {
          this.addSearchResultsToTheSingleSelectValue(item)
        }
      })

    },

    /**
     * Add search results to the multiple select.
     *
     * If we have multiple select.
     * Add search result if we have no selected values.
     * Add search result if selected value id not equal to item id
     *
     * @param item
     * @returns {boolean}
     */
    addSearchResultsToTheMultipleSelectValue(item) {
      if (!this.currentValue) {
        this.searchResults.push(item)
      } else {
        const index = this.currentValue.findIndex(currentValue => currentValue.id === item.id)
        if (index === -1) {
          this.searchResults.push(item)
        }
      }
    },

    /**
     * Add search results to the single select.
     *
     * If we have single select.
     * Add search result if we have no selected values.
     * Add search result if selected value id not equal to item id
     *
     * @param item
     * @returns {boolean}
     */
    addSearchResultsToTheSingleSelectValue(item) {
      if (!this.currentValue || this.currentValue.id !== item.id) {
        this.searchResults.push(item)
      }
    },

    /**
     * Delete selected item.
     */
    deselect() {
      console.log('deselect')
      this.isChanged = true
    },

    optionSelected() {
      console.log('optionSelected')
      this.isChanged = true
    },

    input(input) {
      /**
       * Set current value
       */
      this.currentValue = input

      /**
       * Start new search
       */
      this.search()
    },
  },
}
</script>

<style>
.v-select {
  position: relative;
  min-height: 3rem;
}

.v-select .vs__open-indicator {
  display: none;
}

.v-select.vs--open {
  border: 1px solid #6E78B5;
}

.vs__dropdown-toggle {
  width: 100%;
  display: flex;
  align-items: center;
}

.vs__selected-options {
  width: 100%;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
}

.v-select ul {
  background-color: #fff;
  box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.25);
  border-radius: 0.5rem;
  width: 100%;
  z-index: 10;
  left: 0px;
  top: calc(100% + 1px);
  position: absolute;
}

.v-select ul li {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  padding-left: 1rem;
  padding-right: 1rem;
  cursor: pointer;
}

.v-select ul li:hover {
  background-color: #F1F5F4;
}

.v-select input,
.v-select input:focus {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  line-height: 1.4;
  font-size: 1em;
  outline: none;
  background: none;
  box-shadow: none;
  width: 0;
  max-width: 100%;
  flex-grow: 1;
  z-index: 1;
}

.v-select input::-webkit-search-cancel-button {
  display: none;
}

.vs__actions {
  display: flex;
  align-items: center;
}

.v-select .vs__clear {
  margin-right: 5px;
  display: none;
}

.v-select.vs--open .vs__clear {
  display: inline-block !important;
}

.vs__open-indicator {
  fill: rgba(60, 60, 60, .5);
  transform: scale(1);
  transition: transform .15s cubic-bezier(1, -.115, .975, .855);
  transition-timing-function: cubic-bezier(1, -.115, .975, .855);
}

.v-select .vs__selected {
  border: 1px solid transparent;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem 0.5rem;
  margin: 0.125rem;
  font-weight: 500;
  position: relative;
}

.v-select.v-user.vs--open .vs__selected {
  background-color: #fff;
  border-color: #F1F5F4;
  font-size: .714rem;
}

.v-select .deselect {
  display: none;
}

.v-select.vs--open .deselect {
  display: inline-block;
}

.v-select .vs__deselect {
  position: absolute;
  right: 0.6rem;
  top: calc(50% - 5px);
  margin: 0;
  opacity: 0;
}

.v-select.placeholder-italic input::placeholder {
  font-style: italic;
}

.v-select input {
  padding-left: 0.5rem;
}

.v-select .vs__selected + input {
  padding-left: 0;
}

.v-select.relative-list {
  border: 0;
}

.v-select.relative-list .vs__dropdown-toggle {
  background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAQCAYAAAAMJL+VAAAABGdBTUEAALGPC/xhBQAAAQtJREFUOBG1lEEOgjAQRalbGj2OG9caOACn4ALGtfEuHACiazceR1PWOH/CNA3aMiTaBDpt/7zPdBKy7M/DCL9pGkvxxVp7KsvyJftL5rZt1865M+Ucq6pyyF3hNcI7Cuu+728QYn/JQA5yKaempxuZmQngOwEaYx55nu+1lQh8GIatMGi+01NwBcEmhxBqK4nAPZJ78K0KKFAJmR3oPp8+Iwgob0Oa6+TLoeCvRx+mTUYf/FVBGTPRwDkfLxnaSrRwcH0FWhNOmrkWYbE2XEicqgSa1J0LQ+aPCuQgZiLnwewbGuz5MGoAhcIkCQcjaTBjMgtXGURMVHC1wcQEy0J+Zlj8bKAnY1/UzDe2dbAVqfXn6wAAAABJRU5ErkJggg==');
  background-size: 0.7rem;
  background-repeat: no-repeat;
  background-position: right 0.7rem center;
  border: 1px solid #F1F5F4;
  border-radius: 0.25rem;
  padding: 0 1.5rem 0 0.5rem;
  min-height: 40px;
}

.v-select.relative-list.vs--open .vs__dropdown-toggle {
  border-color: #6E78B5;
}

.v-select.relative-list .vs__dropdown-toggle + ul {
  position: relative;
  top: 0;
}
</style>
