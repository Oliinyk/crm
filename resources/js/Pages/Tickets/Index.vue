<template>
  <div>
    <Head :title="$t('Tickets')" />
    <h1 class="mb-8 font-bold text-dark text-xl">{{ $t('Tickets') }}</h1>
    <div class="pb-2 sm:pb-6 flex items-center flex-wrap justify-between relative">
      <div class="flex items-center flex-wrap w-full sm:w-auto">
        <div class="flex items-center justify-between w-full sm:w-auto">
          <button class="btn-cta-primary inline-flex mr-4 font-semibold" @click="showCreateModal()">
            <icon name="plus" class="w-6 h-6 mr-0.5 fill-light inline"></icon>
            <span>{{ $t('New ticket') }}</span>
          </button>
          <!-- mobile search and filter icons -->
          <div class="flex items-center sm:hidden">
            <div class="flex cursor-pointer" @click="openSearch()">
              <icon name="search" class="w-6 h-6 mr-2.5 fill-gray"></icon>
            </div>
            <filter-menu :fields="fields">
              <button class="inline-flex">
                <icon name="filter" class="w-6 form-icon"></icon>
              </button>
            </filter-menu>
          </div>
        </div>
        <div class="hidden sm:flex">
          <div v-if="false" class="flex items-center flex-wrap">
            <button class="btn-outline inline-flex items-center mr-4 mt-2 sm:mt-0 font-semibold">
              <p class="flex items-center h-6">
                <icon name="edit-without-line" class="w-6 sm:mr-2 fill-dark inline"></icon>
                <span class="hidden sm:inline">{{ $t('Edit') }}</span>
              </p>
            </button>
            <button class="btn-outline inline-flex items-center mr-4 mt-2 sm:mt-0 font-semibold">
              <p class="flex items-center h-6">
                <icon name="trash" class="w-6 sm:mr-2 fill-dark inline"></icon>
                <span class="hidden sm:inline">{{ $t('Delete') }}</span>
              </p>
            </button>
          </div>
          <button class="btn-outline inline-flex mr-4 mt-2 sm:mt-0 font-semibold">
            <p class="flex items-center h-6">
              <icon name="export" class="w-6 sm:mr-2 fill-dark inline"></icon>
              <span class="hidden sm:inline">{{ $t('Export all') }}</span>
            </p>
          </button>
        </div>
      </div>
      <div class="flex flex-wrap-reverse sm:flex-nowrap items-center w-full sm:w-auto">
        <div class="hidden sm:flex">
          <button class="inline-flex items-center mr-4 mt-2 sm:mt-0 font-semibold">
            <icon name="list" class="w-6 sm:mr-2 inline form-icon fill-dark"></icon>
            <span class="hidden sm:inline">{{ $t('List') }}</span>
          </button>
          <button class="inline-flex items-center mr-4 mt-2 sm:mt-0 font-semibold">
            <icon name="geo" class="w-6 sm:mr-2 inline form-icon"></icon>
            <span class="hidden sm:inline text-gray">{{ $t('Plan') }}</span>
          </button>
          <button class="inline-flex items-center mr-4 mt-2 sm:mt-0 font-semibold">
            <icon name="stages" class="w-6 sm:mr-2 inline form-icon"></icon>
            <span class="hidden sm:inline text-gray">{{ $t('Stages') }}</span>
          </button>
        </div>
        <div class="flex items-center w-full search-xs sm:z-1 relative">
          <text-input v-model="form.search"
                      class="relative w-full sm:w-32 sm:max-w-md my-0 mt-2 sm:my-1.5"
                      :class="showSearch?'inline sm:hidden':' hidden sm:inline'"
                      autocomplete="off"
                      type="text"
                      name="search"
                      :placeholder="$t('Search')"
                      :icon="showSearch?'close':'search'"
                      :always-show-icon="true"
          />
        </div>
        <filter-menu :fields="fields" :filters="filters" :search="form.search">
          <button
            class="btn-outline items-center mr-4 mt-2 sm:mt-0 font-semibold rounded-tl-none rounded-bl-none focus:ml-px hidden sm:inline-flex focus:z-10 relative"
          >
            <icon name="filter" class="w-6 sm:mr-2 inline form-icon"></icon>
            <span class="hidden sm:inline">{{ $t('Filter') }}&nbsp;</span>
            <span v-if="getFilterCount()">({{ getFilterCount() }})</span>
          </button>
        </filter-menu>
      </div>
      <!-- mobile button bar -->
      <div class="flex justify-between w-full sm:hidden">
        <div class="flex">
          <div v-if="selectedTicket.length" class="flex items-center flex-wrap">
            <button class="btn-outline inline-flex items-center mr-4 mt-2">
              <p class="flex items-center h-6">
                <icon name="edit-without-line" class="w-6 sm:mr-2 fill-dark inline"></icon>
              </p>
            </button>
            <button class="btn-outline inline-flex items-center mr-4 mt-2">
              <p class="flex items-center h-6">
                <icon name="trash" class="w-6 sm:mr-2 fill-dark inline"></icon>
              </p>
            </button>
          </div>
          <button class="btn-outline inline-flex mr-4 mt-2">
            <p class="flex items-center h-6">
              <icon name="export" class="w-6 sm:mr-2 fill-dark inline"></icon>
            </p>
          </button>
        </div>
        <div class="flex">
          <button class="inline-flex items-center mr-4 mt-2">
            <icon name="list" class="w-6 sm:mr-2 inline form-icon fill-dark"></icon>
          </button>
          <button class="inline-flex items-center mr-4 mt-2">
            <icon name="geo" class="w-6 sm:mr-2 inline form-icon"></icon>
          </button>
          <button class="inline-flex items-center mt-2">
            <icon name="stages" class="w-6 sm:mr-2 inline form-icon"></icon>
          </button>
        </div>
      </div>
    </div>
    <div class="-mb-7 sm:m-0 sm:relative sm:h-0 text-right">
      <table-option-dropdown :close-in-click="false" @close="updateTableFields">
        <icon name="settings"
              class="h-9 w-8 sm:w-12 inline form-icon cursor-pointer bg-white pl-2 pt-1 pb-1 sm:pr-4 sm:absolute top-0 sm:top-9 right-0"
        ></icon>
        <div slot="dropdown" class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded">
          <div class="flex items-center w-full">
            <text-input class=" px-5 my-0 mt-2 w-full sm:my-1.5"
                        autocomplete="off"
                        type="text"
                        name="search"
                        :placeholder="$t('Search')"
            />
          </div>
          <Container drag-handle-selector=".column-drag-handle" @drop="onDrop">
            <Draggable v-for="item in selectedFields" :key="item.id + '-table'" class="relative">
              <div
                class="flex items-center justify-between px-5 py-2 hover:bg-light text-dark text-sm"
              >
                <div class="flex items-center">
                  <checkbox-input v-model="item.show"
                                  class="text-dark"
                                  :disabled-marked="item.disabledMarked"
                                  :label="$t(item.name)"
                                  @input="changeSelectedFields(item.id, item.show)"
                  />
                </div>
                <icon name="dragndrop"
                      class="h-3 w-3 fill-gray inline cursor-move column-drag-handle"
                ></icon>
              </div>
            </Draggable>
          </Container>
        </div>
      </table-option-dropdown>
    </div>
    <ticket-table :tickets="tickets"
                  :show-checkboxes="true"
                  :can="can"
                  :fields="selectedFields"
                  @check="(tickets) => $set(this,'selectedTicket',tickets)"
    />
  </div>
</template>

<script>
import ProjectLayout from '@/Shared/Layouts/ProjectLayout'
import TicketTable from '@/Shared/Tables/TicketTable'
import TableOptionDropdown from '@/Shared/Dropdowns/TableOptionDropdown'
import {Container, Draggable} from 'vue-dndrop'
import Vue from 'vue'
import TicketCreateModal from '@/Shared/Modals/TicketCreateModal'
import axios from 'axios'

import FilterMenu from '@/Shared/Menues/FilterMenu'

export default {
  components: {
    TicketTable,
    Container,
    Draggable,
    TableOptionDropdown,
    FilterMenu,
  },
  layout: ProjectLayout,
  props: {
    filters: Object,
    tickets: Object,
    can: Object,
    ticketTypes: Object,
    fields: Array,
  },
  remember: 'form',
  data() {
    return {
      selectedTicket: [],
      form: {
        search: this.filters.search,
      },
      selectedFields: this.fields,
      showSearch: false,
    }
  },
  methods: {
    changeSelectedFields(id, status) {
      const index = this.fields.findIndex(item => item.id === id)
      let data = this.fields[index]
      data.show = status
      Vue.set(this.fields, index, data)
    },
    openSearch() {
      return this.showSearch = !this.showSearch
    },
    disableAndUnselectCheckbox(disable, unselect) {
      if (disable) {
        this.$set(this.form, unselect, null)
      }
      return disable
    },
    onDrop(dropResult) {
      const elem = this.selectedFields.splice(dropResult.removedIndex, 1)
      this.selectedFields.splice(dropResult.addedIndex, 0, ...elem)
    },
    showCreateModal() {
      this.$modal.show(TicketCreateModal, {'ticketTypes': this.ticketTypes})
    },
    updateTableFields() {
      axios.post(this.route('ticket.table', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.$page.props.project.id,
      }), this.selectedFields)
        .catch((error) => {
          if (error.response) {
            if (error.response.status === 401) {
              this.$inertia.reload()
            }
          } else if (error.request) {
            // The request was made but no response was received
            console.log(error.request)
          } else {
            // Something happened in setting up the request that triggered an Error
            console.log('Error', error.message)
          }
        })
    },
    getFilterCount() {
      return Object.keys(this.filters)
        .filter(element => this.filters[element] && this.filters[element].length)
        .length
    },
  },
}
</script>

<style>
@media (min-width: 640px) {
  .search-xs .form-input {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    padding-left: 2.5rem;
    padding-right: 1rem;
  }

  .search-xs span {
    left: 0;
    right: auto;
    padding-left: 0.75rem;
    padding-right: 0;
  }
}

.text-dark .form-label {
  color: #26293B;
}
</style>
