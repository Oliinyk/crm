<template>
  <div>
    <Table :models="roles"
           :headers="[
             $t('Name'),
             $t('# of users')
           ]"
           :empty-message="$t('No roles found.')"
           @check="check"
    >
      <template #body="{model}">
        <td class="border-t cursor-pointer h-16 sm:h-auto flex sm:table-cell justify-between sm:w-5/12 md:w-2/6 lg:w-1/4">
          <div class="px-6 py-4 flex items-center focus:text-indigo-500 w-full" @click="showUpdateModal(model)">
            {{ model.name }}
          </div>
          <dropdown placement="bottom-end" class="sm:hidden">
            <div class="py-2 flex items-center">
              <icon name="more" class="block w-6 h-6 fill-gray flex items-center"></icon>
            </div>
            <div slot="dropdown" class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded">
              <div class="block px-6 py-2 hover:bg-light text-dark cursor-pointer" @click="showUpdateModal(model)">
                {{ $t('Edit') }}
              </div>
              <div class="block px-6 py-2 hover:bg-light text-dark cursor-pointer" @click="destroy(model.id)">
                {{ $t('Delete') }}
              </div>
            </div>
          </dropdown>
        </td>
        <td class="border-t cursor-pointer h-16 sm:h-auto" @click="showUpdateModal(model)">
          <div class="px-6 py-4 flex items-center h-full">
            {{ model.users_count }}
          </div>
        </td>
        <td class="hidden sm:table-cell items-center relative w-px">
          <dropdown placement="bottom-end">
            <div class="py-4 flex items-center text-left">
              <icon name="more" class="block w-6 h-6 fill-gray flex items-center"></icon>
            </div>
            <div slot="dropdown" class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded">
              <div class="block px-6 py-2 hover:bg-light text-dark cursor-pointer" @click="showUpdateModal(model)">
                {{ $t('Edit') }}
              </div>
              <div class="block px-6 py-2 hover:bg-light text-dark cursor-pointer" @click="destroy(model.id)">
                {{ $t('Delete') }}
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
import RoleCreateModal from '@/Shared/Modals/RoleCreateModal'
import RoleUpdateModal from '@/Shared/Modals/RoleUpdateModal'

export default {
  components: {
    Table,
  },
  layout: Layout,
  props: {
    roles: Object,
    showCheckboxes: Boolean,
    can: Object,
  },
  methods: {
    check(checkedItems) {
      this.$emit('check', checkedItems)
    },
    showUpdateModal(role) {
      this.$modal.show(RoleUpdateModal, {role, 'can': this.can})
    },
    showCreateModal() {
      this.$modal.show(RoleCreateModal, {})
    },
    destroy(id) {
      if (confirm(this.$t('Are you sure you want to delete this role?'))) {
        this.$inertia.delete(this.route('role.destroy', {workspace: this.$page.props.auth.workspace_id, role: id}))
      }
    },
  },
}
</script>
