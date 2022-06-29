<template>
  <div>
    <Table :models="models"
           :headers="[
             $t('Workspace'),
             $t('Invited by'),
             $t('Invited on'),
             $t('Actions'),
           ]"
           :empty-message="$t('No invites found.')"
           :show-checkboxes="false"
           :show-pagination="false"
           @check="check"
    >
      <template #body="{model}">
        <td class="cursor-pointer h-16 sm:h-auto bg-light sm:bg-transparent flex sm:table-cell justify-between">
          <div class="px-6 py-4 flex items-center text-dark">
            <div>
              <div class="flex items-center">
                <workspace-icon :name="model.workspace.name" :src="model.workspace.image.url" />
                <div class="text-dark">{{ model.workspace.name }}</div>
              </div>
            </div>
          </div>
        </td>
        <td class="cursor-pointer h-16 sm:h-auto flex sm:table-cell justify-between">
          <div class="px-6 py-4 flex items-center text-dark">{{ model.author.name }}</div>
        </td>
        <td class="cursor-pointer h-16 sm:h-auto flex sm:table-cell justify-between">
          <div class="px-6 py-4 flex items-center text-dark">{{ model.created_at }}</div>
        </td>
        <td class="sm:w-px px-1 sm:px-6 h-16 sm:h-auto">
          <div class="flex h-full px-6 sm:px-0">
            <button class="text-secondary font-semibold pr-2" @click="accept(model)">
              <span>{{ $t('Accept') }}</span>
            </button>
            <button class="text-secondary font-semibold pl-2" @click="remove(model)">
              <span>{{ $t('Remove') }}</span>
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
    invitations: Array,
  },
  data() {
    return {
      models:{data:this.invitations},
    }
  },
  methods: {
    check(checkedItems) {
      this.$emit('check', checkedItems)
    },
    accept(model) {
      if (confirm(this.$t('Are you sure you want to delete this invitation?'))) {
        this.$inertia.post(this.route('invitation.accept.user', {
          workspace: this.$page.props.auth.workspace_id,
          token: model.token,
        }), {
          onSuccess: () => {
            this.$emit('close')
          },
        })
      }
    },
    remove(model) {
      if (confirm(this.$t('Are you sure you want to delete this invitation?'))) {
        this.$inertia.delete(this.route('invitation.decline.workspace', {
          workspace: this.$page.props.auth.workspace_id,
          token: model.token,
        }), {
          onSuccess: () => {
            this.$emit('close')
          },
        })
      }
    },
  },
}
</script>
