<template>
  <div>
    <Table :models="projects"
           :headers="[
             $t('Name'),
             $t('Lead'),
           ]"
           :empty-message="$t('No projects found.')"
           :show-checkboxes="showCheckboxes"
           @check="check"
    >
      <template #body="{model}">
        <td class="h-16 sm:h-auto flex sm:table-cell justify-between sm:w-5/12 md:w-2/6 lg:w-1/4">
          <Link class="cursor-pointer px-6 py-2 sm:py-4 flex items-center text-dark"
                :href="route('ticket.index', {workspace: $page.props.auth.workspace_id, project:model.id})"
          >
            <project-icon :name="model.name" />
            {{ model.name }}
          </Link>
          <dropdown placement="bottom-end" class="sm:hidden">
            <div class="py-2 flex items-center">
              <icon name="more" class="block w-6 h-6 fill-gray flex items-center"></icon>
            </div>
            <div slot="dropdown" class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded">
              <Link class="block px-6 py-2 hover:bg-light text-dark"
                    href=""
              >
                {{ $t('Project Settings') }}
              </Link>
              <Link class="block px-6 py-2 hover:bg-light text-dark"
                    :href="route('user.index', {workspace: $page.props.auth.workspace_id})"
              >
                {{ $t('Move to trash') }}
              </Link>
            </div>
          </dropdown>
        </td>
        <td class="h-16 sm:h-auto">
          <Link class="px-6 py-2 sm:py-4 flex items-center text-dark" @click="showUserModal(model.owner)">
            <user-icon
              :full-name="model.owner.full_name"
              :src="model.owner.image.url"
              class="cursor-pointer"
            />
            <div class="cursor-pointer">{{ model.owner.full_name }}</div>
          </Link>
        </td>
        <td class="hidden sm:table-cell items-center relative w-px">
          <dropdown placement="bottom-end">
            <div class="py-4 flex items-center text-left project-more-menu">
              <icon name="more" class="block w-6 h-6 fill-gray flex items-center"></icon>
            </div>
            <div slot="dropdown" class="py-2 bg-white rounded shadow rounded-b-3xl rounded-t-none sm:rounded">
              <Link class="block px-6 py-2 hover:bg-light text-dark"
                    href=""
              >
                {{ $t('Project Settings') }}
              </Link>
              <div class="block px-6 py-2 hover:bg-light text-dark" @click="destroy(model.id)">
                {{ $t('Move to trash') }}
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
import UserUpdateModal from '@/Shared/Modals/UserUpdateModal'
import UserShowModal from '@/Shared/Modals/UserShowModal'

export default {
  components: {
    Table,
  },
  layout: Layout,
  props: {
    projects: Object,
    showCheckboxes: Boolean,
    can: Object,
    users: Object,
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
    destroy(projectId) {
      if (confirm(this.$t('Are you sure you want to delete this project?'))) {
        this.$inertia.delete(this.route('project.destroy', {
          workspace: this.$page.props.auth.workspace_id,
          project: projectId,
        }))
      }
    },
  },
}
</script>
