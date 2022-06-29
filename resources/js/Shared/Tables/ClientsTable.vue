<template>
  <div>
    <Table :models="clients"
           :headers="[
             $t('Client'),
             $t('Status'),
             $t('# of projects'),
             $t('City'),
           ]"
           :empty-message="$t('No users found.')"
           :show-checkboxes="showCheckboxes"
           @check="check"
    >
      <template #body="{model}">
        <td class="cursor-pointer h-16 sm:h-auto bg-light sm:bg-transparent flex sm:table-cell justify-between"
            @click="showUpdateModal(model)"
        >
          <div class="px-6 py-4 flex items-center text-dark">{{ model.name }}</div>
        </td>
        <td class="cursor-pointer h-16 sm:h-auto" @click="showUpdateModal(model)">
          <div class="px-6 py-4 flex items-center text-dark">
            {{ model.status }}
          </div>
        </td>
        <td class="cursor-pointer h-16 sm:h-auto " @click="showUpdateModal(model)">
          <div class="px-6 py-4 flex items-center text-dark">
            0
          </div>
        </td>
        <td class="cursor-pointer h-16 sm:h-auto " @click="showUpdateModal(model)">
          <div class="px-6 py-4 flex items-center text-dark">
            {{ model.city }}
          </div>
        </td>
      </template>
    </Table>
  </div>
</template>

<script>
import Layout from '@/Shared/Layouts/Layout'
import Table from '@/Shared/Tables/Table'
import ClientUpdateModal from '@/Shared/Modals/ClientUpdateModal'
import ClientShowModal from '@/Shared/Modals/ClientShowModal'

export default {
  components: {
    Table,
  },
  layout: Layout,
  props: {
    clients: Object,
    showCheckboxes: Boolean,
    can: Object,
  },
  methods: {
    check(checkedItems) {
      this.$emit('check', checkedItems)
    },
    showUpdateModal(client) {
      if (this.can.add_clients) {
        this.$modal.show(ClientUpdateModal, {client, can: this.can})
        return
      }
      this.$modal.show(ClientShowModal, {client, can: this.can})
    },
  },
}
</script>
