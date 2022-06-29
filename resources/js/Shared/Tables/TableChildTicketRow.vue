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
    </div>
    <div class="overflow-x-auto my-2 sm:my-5">
      <table
        class="w-full flex flex-row flex-no-wrap sm:bg-white rounded-none sm:rounded-lg overflow-hidden sm:shadow-lg hidden"
      >
        <thead>
          <tr>
            <td class="p-0 sm:pl-6 border-b">
              <tr v-for="item in models.data.length" :key="item"
                  class="text-left text-gray font-bold text-sm flex flex-col flex-no-wrap sm:table-row"
              >
                <th v-if="showCheckboxes" class="w-6 p-0 hidden sm:table-cell">
                  <div class="group" @click="checkAll()">
                    <icon v-if="!isCheckedAll" name="checkbox-blank"
                          class="fill-gray cursor-pointer group-hover:fill-accent-dark"
                    />
                    <icon v-else name="checkbox-marked"
                          class="fill-secondary cursor-pointer group-hover:fill-accent-dark"
                    />
                  </div>
                </th>
                <th v-for="(header, headerIndex) in headers"
                    :key="header"
                    class="px-1 sm:px-3 h-16 flex justify-center items-center sm:table-cell"
                    :class="[headerIndex === 0 ? 'bg-light sm:bg-white' : 'flex-row-reverse', {'justify-between': showCheckboxes}]"
                    @click="sortRows(header)"
                >
                  <div class="flex items-center">
                    <!-- ID -->
                    <p v-if="header === 'ID'" class="w-20 truncate flex align-center">
                      {{ header }}
                      <sortingIcon v-if="true" :direction="sort.direction" :is-active="sort.orderBy === header.toLowerCase()" />
                    </p>
                    <!-- Title -->
                    <p v-else-if="header === 'Title'" class="sm:w-64 flex align-center">
                      {{ header }}
                      <sortingIcon v-if="true" :direction="sort.direction" :is-active="sort.orderBy === header.toLowerCase()" />
                    </p>
                    <!-- Assignee/Status/Watchers -->
                    <p v-else-if="checkHeaders(header)"
                       class="sm:min-w-200 flex align-center"
                    >
                      {{ header }}
                      <sortingIcon v-if="true" :direction="sort.direction" :is-active="sort.orderBy === header.toLowerCase()" />
                    </p>
                    <p v-else class="sm:w-48 flex align-center">
                      {{ header }}
                      <sortingIcon v-if="true" :direction="sort.direction" :is-active="sort.orderBy === header.toLowerCase()" />
                    </p>
                  </div>
                </th>
              </tr>
            </td>
          </tr>
        </thead>
        <tbody class="flex-1 sm:flex-none">
          <tr v-for="(model, index) in models.data" :key="model.id"
              class="flex flex-col flex-no-wrap relative"
              :class="model.child_tickets.length ? 'sm:flex sm:flex-wrap' : 'sm:table-row'"
          >
            <td v-if="!model.parent_ticket" class="relative p-0 sm:pl-6 sm:border-b hover:bg-light mw-100">
              <tr class="flex flex-col sm:table-row">
                <td v-if="showCheckboxes" class="w-6 p-0 sm:table-cell items-center">
                  <div v-if="model.child_tickets.length"
                       class="absolute top-0 left-0 h-16 sm:h-full sm:flex hidden items-center cursor-pointer"
                       @click="toggleChild(model.id)"
                  >
                    <icon name="arrow"
                          class="block w-4 h-4 fill-gray-light flex items-center fill-gray ml-1 mr-2 transform"
                          :class="{'-rotate-90':!isOpen(model.id)}"
                    />
                  </div>
                  <div class="group hidden sm:block" @click="check(model.id)">
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
            </td>
            <td v-for="(child, childIndex) in model.child_tickets"
                v-show="isOpen(model.id)"
                :key="child.id + '_' + childIndex"
                class="border-b pl-6 hover:bg-light relative"
            >
              <tr>
                <slot name="body" :model="child" :index="index + '_' + childIndex" />
              </tr>
            </td>
          </tr>
          <tr v-if="models.data.length === 0">
            <td class="border-t px-6 py-4 text-dark" colspan="3">
              {{ emptyMessage }}
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Start mobile view -->
      <div class="flex flex-wrap sm:hidden">
        <div v-for="(item ,modelIndex) in models.data.length" :key="item.id"
             :class="'order-' + (modelIndex + 1)"
             class="w-1/3"
        >
          <div v-for="(header, headerIndex) in headers"
               :key="header"
               class="px-1 h-16 flex justify-start items-center border-b "
               :class="[headerIndex === 0 ? 'bg-light pl-1' : 'pl-10']"
          >
            <div v-if="showCheckboxes && headerIndex === 0" class="block pr-3">
              <div class="group" @click="checkByIndex(modelIndex)">
                <icon v-if="!isCheckedByIndex(modelIndex)" name="checkbox-blank"
                      class="fill-gray cursor-pointer group-hover:fill-accent-dark"
                />
                <icon v-if="isCheckedByIndex(modelIndex)" name="checkbox-marked"
                      class="fill-secondary cursor-pointer group-hover:fill-accent-dark"
                />
              </div>
            </div>
            <!-- ID -->
            <p v-if="header === 'ID'" class="w-20 truncate">{{ header }}</p>
            <!-- Title -->
            <p v-else-if="header === 'Title'">{{ header }}</p>
            <!-- Assignee/Status/Watchers -->
            <p v-else-if="checkHeaders(header)">
              {{ header }}
            </p>
            <p v-else>{{ header }}</p>
          </div>
        </div>
        <div v-for="(model, index) in models.data" :key="model.id"
             class="flex flex-col flex-no-wrap relative w-2/3 mb-4"
             :class="'order-'+(index+1)"
        >
          <div v-if="!model.parent_ticket" class="relative p-0 mw-100 parent-item">
            <div class="flex flex-col overflow-hidden">
              <slot name="body" :model="model" :index="index" />
            </div>
          </div>
          <!-- child ticket row -->
          <div v-if="model.child_tickets.length" class="border-b border-white p-0 h-16 bg-accent -ml-1/2">
            <div class="flex items-center h-full" @click="toggleChild(model.id)">
              <div class="h-16 flex items-center cursor-pointer">
                <icon name="arrow"
                      class="block w-4 h-4 fill-gray-light flex items-center fill-gray ml-1 mr-2 transform"
                      :class="{'-rotate-90':!isOpen(model.id)}"
                />
              </div>
              <icon name="parent-tickets" class="w-5 h-5 fill-gray-light flex items-center flex-none ml-1 mr-1"></icon>
              <p>{{ $t('Subtasks') }} ({{ model.child_tickets.length }})</p>
            </div>
          </div>
          <div v-for="(child, childIndex) in model.child_tickets"
               v-show="isOpen(model.id)"
               :key="child.id + '_' + childIndex"
               class="border-b border-white relative flex -ml-1/3 bg-accent child-item"
          >
            <slot name="body" :model="child" :index="index + '_' + childIndex" />
          </div>
        </div>
        <div v-if="models.data.length === 0" class="w-full">
          <div class="border-t px-6 py-4 text-dark">
            {{ emptyMessage }}
          </div>
        </div>
      </div>
      <!-- End mobile view -->
    </div>
    <pagination class="mt-6"
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
import sortingIcon from '@/Shared/Icons/SortingIcon'
import throttle from 'lodash/throttle'
import pickBy from 'lodash/pickBy'

export default {
  components: {
    Pagination,
    sortingIcon,
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
    icon: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      isCheckedAll: false,
      checkedItems: [],
      openedTickets: [],
      sort: {
        orderBy: null,
        direction: null,
      },
    }
  },
  watch: {
    checkedItems: {
      deep: true,
      handler: function () {
        this.$emit('check', this.checkedItems)
      },
    },
    sort: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get(this.route('ticket.index', {
          workspace: this.$page.props.auth.workspace_id,
          project: this.$page.props.project.id,
        }), pickBy(this.sort), {preserveState: true})
      }, 150),
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
    toggleChild(id) {
      const index = this.openedTickets.findIndex(item => item === id)
      if (index === -1) {
        this.openedTickets.push(id)
      } else {
        this.openedTickets.splice(index, 1)
      }
    },
    isOpen(id) {
      return this.openedTickets.includes(id)
    },
    checkHeaders(header) {
      return [
        this.$fieldTypes.TYPE_ASSIGNEE,
        this.$fieldTypes.TYPE_STATUS,
        this.$fieldTypes.TYPE_WATCHERS,
      ].includes(header.toLowerCase())
    },
    sortRows(header) {
      if(this.sort.orderBy !== header.toLowerCase()) {
        this.sort.direction = null
      }
      this.sort.orderBy = header.toLowerCase()
      if(this.sort.direction === 'desc') {
        this.sort.direction = null
        this.sort.orderBy = null
        return
      }
      this.sort.direction === 'asc' ? this.sort.direction = 'desc' : this.sort.direction = 'asc'
    },
  },
}
</script>
