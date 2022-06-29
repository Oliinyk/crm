<template>
  <div>
    <Table :models="ticketTypes"
           :headers="[
             $t('Name'),
             $t('Author'),
             $t('Updated'),
           ]"
           :empty-message="$t('No ticket types found.')"
           :show-checkboxes="showCheckboxes"
           @check="check"
    >
      <template #body="{model}">
        <td class="cursor-pointer h-16 sm:h-auto bg-light sm:bg-transparent flex sm:table-cell justify-between sm:w-1/4"
            @click="showUpdateModal(model)"
        >
          <div class="px-6 py-4 flex items-center text-dark">
            <p>{{ model.name }}</p>
            <icon v-if="model.deleted_at" name="disable" class="w-5 h-5 ml-2 fill-orange inline"></icon>
          </div>
        </td>
        <td class="cursor-pointer h-16 sm:h-auto" @click="showUpdateModal(model)">
          <div class="px-6 py-4 flex items-center text-dark">
            <p>{{ model.author }}</p>
          </div>
        </td>
        <td class="cursor-pointer h-16 sm:h-auto" @click="showUpdateModal(model)">
          <div class="px-6 py-4 flex items-center text-dark">
            <p>{{ model.updated_at }}</p>
          </div>
        </td>
        <td class="table-cell items-center absolute top-1 right-0 sm:relative sm:top-auto sm:right-auto sm:text-right sm:w-px">
          <dropdown placement="bottom-end">
            <div class="py-4 flex items-center text-left">
              <icon name="more" class="block w-6 h-6 fill-gray flex items-center"></icon>
            </div>
            <div slot="dropdown"
                 class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded"
            >
              <div class="block px-6 py-2 hover:bg-light text-dark cursor-pointer" @click="copy(model)">
                {{ $t('Copy') }}
              </div>
              <div class="block px-6 py-2 hover:bg-light text-dark cursor-pointer"
                   @click="showUpdateModal(model)"
              >
                {{ $t('Edit') }}
              </div>
              <div v-if="model.deleted_at == null"
                   class="block px-6 py-2 hover:bg-light text-dark cursor-pointer"
                   @click="disable(model)"
              >
                {{ $t('Disable') }}
              </div>
            </div>
          </dropdown>
        </td>
      </template>
    </Table>
  </div>
</template>

<script>
import Layout from '@/Shared/Layouts/Layout'
import Table from '@/Shared/Tables/Table'
import TicketTypeUpdateModal from '@/Shared/Modals/TicketTypeUpdateModal'

export default {
  components: {
    Table,
  },
  layout: Layout,
  props: {
    ticketTypes: Object,
    showCheckboxes: Boolean,
    can: Object,
  },
  data() {
    return {
      selectedTickets: [],
    }
  },
  methods: {
    check(checkedItems) {
      this.$emit('check', checkedItems)
    },
    copy(model) {
      this.$inertia.post(this.route('ticket-type.copy', {
        workspace: this.$page.props.auth.workspace_id,
        ticketType:model.id,
      }))
    },
    disable(model) {
      if (confirm(this.$t('Are you sure you want to disable this ticket type?'))) {
        this.$inertia.delete(
          this.route('ticket-type.destroy', {
            workspace: this.$page.props.auth.workspace_id,
            '_query': this.route().params,
          }),
          {data: {ids: [model.id]}},
        )
      }
    },
    showUpdateModal(ticketType) {
      this.$modal.show(TicketTypeUpdateModal, {ticketType, can: this.can})
    },
  },
}
</script>
