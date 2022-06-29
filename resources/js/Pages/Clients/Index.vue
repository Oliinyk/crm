<template>
  <div>
    <Head :title="$t('Clients')" />
    <h1 class="mb-8 font-bold text-dark text-xl">{{ $t('Clients') }}</h1>
    <div class="pb-2 sm:pb-6 flex items-center flex-wrap"
         :class="can.add_clients ? 'justify-between' : 'justify-end'"
    >
      <button v-if="can.add_clients" class="btn-cta-primary inline-flex" @click="showCreateModal">
        <icon name="plus" class="w-6 h-6 mr-2.5 fill-light inline"></icon>
        <span>{{ $t('Create') }}</span>&nbsp;
        <span class="hidden md:inline">{{ $t('Client') }}</span>
      </button>
      <div class="flex" @click="openSearch()">
        <icon name="search" class="w-6 h-6 mr-2.5 fill-gray inline sm:hidden"></icon>
      </div>
      <div class="flex flex-wrap-reverse sm:flex-nowrap items-center w-full sm:w-auto">
        <div class="">
          <button v-if="selectedClients.length && can.delete_clients"
                  class="btn-outline inline-flex mr-4 mt-2 sm:mt-0"
                  @click="showDeleteClientModal"
          >
            <icon name="trash" class="w-6 h-6 mr-0.5 fill-dark inline"></icon>
            <span class="hidden sm:inline">{{ $t('Delete') }}&nbsp;({{ selectedClients.length }})</span>
          </button>
        </div>

        <div class="flex items-center w-full">
          <text-input v-model="form.search"
                      class="relative w-full max-w-md my-0 mt-2 sm:my-1.5"
                      :class="showSearch?'inline sm:hidden':' hidden sm:inline mr-4'"
                      autocomplete="off"
                      type="text"
                      name="search"
                      :placeholder="$t('Search…')"
          />
        </div>
      </div>
    </div>
    <clients-table :clients="clients"
                   :show-checkboxes="can.delete_clients"
                   :can="can"
                   @check="(clients)=>$set(this,'selectedClients',clients)"
    />
  </div>
</template>

<script>
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layouts/Layout'
import throttle from 'lodash/throttle'
import ClientCreateModal from '@/Shared/Modals/ClientCreateModal'
import ClientsTable from '../../Shared/Tables/ClientsTable'

export default {
  components: {
    ClientsTable,
  },
  layout: Layout,
  props: {
    filters: Object,
    clients: Object,
    can: Object,
  },
  data() {
    return {
      isCheckedAll: false,
      selectedClients: [],
      form: {
        search: this.filters.search,
        trashed: this.filters.trashed,
      },
      showSearch: false,
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get(this.route('client.index', {workspace: this.$page.props.auth.workspace_id}), pickBy(this.form), {preserveState: true})
      }, 150),
    },
  },
  methods: {
    showCreateModal() {
      this.$modal.show(ClientCreateModal)
    },
    showDeleteClientModal() {
      if (confirm('Are you sure you want to delete this client?')) {
        this.$inertia.delete(this.route('client.destroy', {
          workspace: this.$page.props.auth.workspace_id,
          ids: this.selectedClients,
        }))
        this.selectedClients.splice(0)
      }
    },
    openSearch() {
      return this.showSearch = !this.showSearch
    },
  },
}
</script>
