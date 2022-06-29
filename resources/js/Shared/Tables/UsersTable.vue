<template>
  <div>
    <Table :models="users"
           :headers="[
             $t('User'),
             $t('Email'),
             $t('Role')
           ]"
           :empty-message="$t('No users found.')"
           :show-checkboxes="showCheckboxes"
           @check="check"
    >
      <template #body="{model}">
        <td class="h-16 sm:h-auto bg-light sm:bg-transparent flex sm:table-cell justify-between xl:w-1/4 user-name">
          <div class="px-6 py-4 flex items-center text-dark cursor-pointer" @click="showUserModal(model)">
            <user-icon
              :full-name="model.full_name"
              :src="model.image.url"
            />
            {{ model.full_name }}
            <icon v-if="model.deleted_at" name="trash"
                  class="flex-shrink-0 w-3 h-3 fill-gray ml-2"
            />
          </div>
        </td>
        <td class="h-16 sm:h-auto xl:w-1/4">
          <div class="px-6 py-4 flex items-center text-dark">
            {{ model.email }}
          </div>
        </td>
        <td class="h-16 sm:h-auto">
          <div v-for="role in model.roles" :key="role.id" class="px-6 py-4 flex items-center text-dark">
            {{ role.name }}
          </div>
        </td>
      </template>
    </Table>
  </div>
</template>

<script>
import Layout from '@/Shared/Layouts/Layout'
import Table from '@/Shared/Tables/Table'
import UserUpdateModal from '@/Shared/Modals/UserUpdateModal'
import UserShowModal from '@/Shared/Modals/UserShowModal'

export default {
  components: {
    Table,
  },
  layout: Layout,
  props: {
    users: Object,
    showCheckboxes: Boolean,
  },
  methods: {
    check(checkedItems) {
      this.$emit('check', checkedItems)
    },
    showUserModal(user) {
      if (user.id === this.$page.props.auth.user.data.id) {
        this.$modal.show(UserUpdateModal, {user: this.$page.props.auth.user})
        return
      }
      this.$modal.show(UserShowModal, {user: user})
    },
  },
}
</script>
