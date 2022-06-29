<template>
  <div>
    <div class="flex justify-between sm:hidden">
      <div v-if="showCheckboxes" class="px-1">
        <div class="group" @click="checkAll()">
          <icon v-if="!isCheckedAll" name="checkbox-blank"
                class="fill-gray cursor-pointer group-hover:fill-accent-dark"
          />
          <icon v-else name="checkbox-marked"
                class="fill-secondary cursor-pointer group-hover:fill-accent-dark"
          />
        </div>
      </div>
      <!-- <div v-if="icon" @click="$emit('iconClick')">
        <icon :name="icon" class="w-6 inline form-icon cursor-pointer"></icon>
      </div> -->
    </div>
    <div class="overflow-x-auto my-2 sm:my-5">
      <table
        class="w-full flex flex-row flex-no-wrap sm:bg-white rounded-none sm:rounded-lg overflow-hidden sm:shadow-lg"
      >
        <thead>
          <tr v-for="(item,modelIndex) in models.data.length" :key="item"
              class="text-left text-gray font-bold text-sm border-b flex flex-col flex-no-wrap sm:table-row"
          >
            <th v-if="showCheckboxes" class="hidden sm:table-cell">
              <div class="group" @click="checkAll()">
                <icon v-if="!isCheckedAll" name="checkbox-blank"
                      class="fill-gray cursor-pointer group-hover:fill-accent-dark"
                />
                <icon v-else name="checkbox-marked"
                      class="fill-secondary cursor-pointer group-hover:fill-accent-dark"
                />
              </div>
            </th>
            <th v-for="(header,headerIndex) in headers"
                :key="header"
                class="px-1 sm:px-6 h-16 flex justify-center items-center sm:table-cell"
                :class="[headerIndex===0 ? 'bg-light sm:bg-white' : 'flex-row-reverse', {'justify-between': showCheckboxes}]"
            >
              <div v-if="showCheckboxes && headerIndex === 0" class="block sm:hidden pr-3">
                <div class="group" @click="checkByIndex(modelIndex)">
                  <icon v-if="!isCheckedByIndex(modelIndex)" name="checkbox-blank"
                        class="fill-gray cursor-pointer group-hover:fill-accent-dark"
                  />
                  <icon v-if="isCheckedByIndex(modelIndex)" name="checkbox-marked"
                        class="fill-secondary cursor-pointer group-hover:fill-accent-dark"
                  />
                </div>
              </div>
              <div>{{ header }}</div>
            </th>
          </tr>
        </thead>
        <tbody class="flex-1 sm:flex-none">
          <tr v-for="(model, index) in models.data" :key="model.id"
              class="border-b flex flex-col flex-no-wrap sm:table-row hover:bg-light relative"
          >
            <td v-if="showCheckboxes" class="w-px hidden sm:table-cell items-center">
              <div class="group" @click="check(model.id)">
                <icon v-if="!isChecked(model.id)" name="checkbox-blank"
                      class="fill-gray cursor-pointer group-hover:fill-accent-dark"
                />
                <icon v-if="isChecked(model.id)" name="checkbox-marked"
                      class="fill-secondary cursor-pointer group-hover:fill-accent-dark"
                />
              </div>
            </td>
            <slot name="body" :model="model" :index="index" />
          </tr>
          <tr v-if="models.data.length === 0">
            <td class="border-t px-6 py-4 text-dark" colspan="3">
              {{ emptyMessage }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <pagination v-if="showPagination"
                class="mt-6"
                :first-page-url="models.links.first"
                :last-page-url="models.links.last"
                :prev-page-url="models.links.prev"
                :next-page-url="models.links.next"
                :current-page="models.meta.current_page"
                :last-page="models.meta.last_page"
                :from="models.meta.from"
                :total="models.meta.total"
                :to="models.meta.to"
    />
  </div>
</template>

<script>
import Layout from '@/Shared/Layouts/Layout'
import Pagination from '@/Shared/Pagination'

export default {
  components: {
    Pagination,
  },
  layout: Layout,
  props: {
    models: Object,
    headers: Array,
    emptyMessage: String,
    showCheckboxes: {
      type: Boolean,
      default: false,
    },
    showPagination: {
      type: Boolean,
      default: true,
    },
    icon: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      isCheckedAll: false,
      checkedItems: [],
    }
  },
  watch: {
    checkedItems: {
      deep: true,
      handler: function () {
        this.$emit('check', this.checkedItems)
      },
    },
  },
  methods: {
    isChecked(item) {
      return this.checkedItems.includes(item)
    },
    isCheckedByIndex(index) {
      return this.isChecked(this.models.data[index].id)
    },
    check(item) {
      const index = this.checkedItems.indexOf(item)
      if (index !== -1) {
        this.checkedItems.splice(index, 1)
      } else {
        this.checkedItems.push(item)
      }
      this.isCheckedAll = this.checkedItems.length === this.models.data.length
    },
    checkAll() {
      if (!this.isCheckedAll) {
        this.isCheckedAll = true
        return this.models.data.forEach(item => this.checkedItems.push(item.id))
      }
      this.isCheckedAll = false
      this.checkedItems.splice(0)
    },
    checkByIndex(index) {
      this.check(this.models.data[index].id)
    },
  },
}
</script>

<style>
@media (min-width: 640px) {
  table {
    display: inline-table !important;
  }

  thead tr:not(:first-child) {
    display: none;
  }
}
</style>
