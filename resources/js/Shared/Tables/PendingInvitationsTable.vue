<template>
  <div>
    <Table :models="invitations"
           :headers="[
             $t('Email'),
             $t('Invited by'),
             $t('Invited on'),
             $t('Actions'),
           ]"
           :empty-message="$t('No invites found.')"
           :show-checkboxes="false"
           @check="check"
    >
      <template #body="{model}">
        <td class="cursor-pointer h-16 sm:h-auto bg-light sm:bg-transparent flex sm:table-cell justify-between">
          <div class="px-6 py-4 flex items-center text-dark">{{ model.email }}</div>
        </td>
        <td class="cursor-pointer h-16 sm:h-auto flex sm:table-cell justify-between">
          <div class="px-6 py-4 flex items-center text-dark">{{ model.author.name }}</div>
        </td>
        <td class="cursor-pointer h-16 sm:h-auto flex sm:table-cell justify-between">
          <div class="px-6 py-4 flex items-center text-dark">{{ model.created_at }}</div>
        </td>
        <td class="sm:w-px px-1 sm:px-6 h-16 sm:h-auto">
          <div class="flex h-full px-6 sm:px-0">
            <button class="text-secondary font-semibold pr-2" @click="resend(model)">
              <span>{{ $t('Resend') }}</span>
            </button>
            <button class="text-secondary font-semibold pl-2" @click="cancel(model)">
              <span>{{ $t('Cancel') }}</span>
            </button>
          </div>
        </td>
      </template>
    </Table>
  </div>
</template>

<script>
import Layout from '@/Shared/Layouts/Layout'
import Table from '@/Shared/Tables/Table'

export default {
  components: {
    Table,
  },
  layout: Layout,
  props: {
    invitations: Object,
  },
  methods: {
    check(checkedItems) {
      this.$emit('check', checkedItems)
    },
    cancel(invitation) {
      this.$inertia.delete(this.route('invitation.decline.user', {
        workspace: this.$page.props.auth.workspace_id,
        token: invitation.token,
      }), {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
    resend(invitation) {
      let route = this.route('invitation.store', {workspace: this.$page.props.auth.workspace_id})
      this.$inertia.post(route, {
        email: invitation.email,
      }, {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
  },
}
</script>
