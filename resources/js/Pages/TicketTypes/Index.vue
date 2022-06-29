<template>
  <div>
    <Head :title="$t('Ticket Types')" />
    <h1 class="mb-8 font-bold text-dark text-xl">{{ $t('Ticket Types') }}</h1>
    <div class="pb-2 sm:pb-6 flex items-center flex-wrap justify-between">
      <button class="btn-cta-primary inline-flex" @click="showCreateModal()">
        <icon name="plus" class="w-6 h-6 mr-0.5 fill-light inline"></icon>
        <span>{{ $t('New ticket type') }}</span>
      </button>
      <div class="flex" @click="openSearch()">
        <icon name="search" class="w-6 h-6 mr-2.5 fill-gray inline sm:hidden"></icon>
      </div>
      <div class="flex flex-wrap-reverse sm:flex-nowrap items-center w-full sm:w-auto">
        <div class="flex">
          <button v-if="isOnlyOneItemSelected"
                  class="btn-outline inline-flex items-center mr-4 mt-2 sm:mt-0"
                  @click="copy"
          >
            <icon name="copy" class="w-6 sm:mr-2 fill-dark inline"></icon>
            <span class="hidden sm:inline">{{ $t('Copy') }}</span>
          </button>
          <button v-if="isOnlyOneItemSelected"
                  class="btn-outline inline-flex items-center mr-4 mt-2 sm:mt-0"
                  @click="showUpdateModal()"
          >
            <icon name="edit-without-line" class="w-6 sm:mr-2 fill-dark inline"></icon>
            <span class="hidden sm:inline">{{ $t('Edit') }}</span>
          </button>
          <button v-if="selectedTicketTypes.length && isSelectedItemsDisabled"
                  class="btn-outline inline-flex items-center mr-4 mt-2 sm:mt-0" @click="disable"
          >
            <icon name="disable" class="w-6 sm:mr-2 fill-dark inline"></icon>
            <span class="hidden sm:inline">
              {{ $t('Disable') }}&nbsp;({{ selectedTicketTypes.length }})
            </span>
          </button>
        </div>
        <div class="flex items-center w-full">
          <text-input v-model="form.search"
                      class="relative w-full sm:max-w-md my-0 mt-2 sm:my-1.5"
                      :class="showSearch?'inline sm:hidden':' hidden sm:inline mr-4'"
                      autocomplete="off"
                      type="text"
                      name="search"
                      :placeholder="$t('Search…')"
          />
        </div>
      </div>
    </div>
    <ticket-type-table :ticket-types="ticketTypes"
                       :show-checkboxes="true"
                       :can="can"
                       @check="(ticketTypes)=>$set(this,'selectedTicketTypes',ticketTypes)"
    />
  </div>
</template>

<script>
import Layout from '@/Shared/Layouts/Layout'
import TicketTypeCreateModal from '@/Shared/Modals/TicketTypeCreateModal'
import TicketTypeTable from '@/Shared/Tables/TicketTypeTable'
import throttle from 'lodash/throttle'
import pickBy from 'lodash/pickBy'
import TicketTypeUpdateModal from '@/Shared/Modals/TicketTypeUpdateModal'

export default {
  components: {
    TicketTypeTable,
  },
  layout: Layout,
  props: {
    filters: Object,
    ticketTypes: Object,
    can: Object,
  },
  data() {
    return {
      isCheckedAll: false,
      selectedTicketTypes: [],
      form: {
        search: this.filters.search,
        trashed: this.filters.trashed,
      },
      showSearch: false,
    }
  },
  computed: {
    isOnlyOneItemSelected() {
      return this.selectedTicketTypes.length === 1
    },
    isSelectedItemsDisabled() {
      return this.selectedTicketTypes.every(id => {
        return !this.ticketTypes.data.find(ticketType => ticketType.id === id).deleted_at
      })
    },
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get(this.route('ticket-type.index', {
          workspace: this.$page.props.auth.workspace_id,
        }), pickBy(this.form), {preserveState: true})
      }, 150),
    },
  },
  methods: {
    showCreateModal() {
      this.$modal.show(TicketTypeCreateModal, {})
    },
    openSearch() {
      return this.showSearch = !this.showSearch
    },
    disable() {
      if (confirm(this.$t('Are you sure you want to disable this ticket type?'))) {
        this.$inertia.delete(
          this.route('ticket-type.destroy', {
            workspace: this.$page.props.auth.workspace_id,
            '_query': this.route().params,
          }),
          {
            data: {ids: this.selectedTicketTypes},
            preserveState: false,
          },
        )
      }
    },
    showUpdateModal() {
      let ticketType = this.ticketTypes.data.find(ticketType => ticketType.id === this.selectedTicketTypes[0])
      this.$modal.show(TicketTypeUpdateModal, {ticketType, can: this.can})
    },
    copy() {
      this.$inertia.post(this.route('ticket-type.copy', {
        workspace: this.$page.props.auth.workspace_id,
        ticketType: this.selectedTicketTypes[0],
      }))
    },
  },
}
</script>
