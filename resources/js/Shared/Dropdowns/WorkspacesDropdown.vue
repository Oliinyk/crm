<template>
  <div class="flex items-center group bg-dark">
    <dropdown class="md:w-56 md:pl-0 pl-6" placement="bottom-end" :show-overlay="false">
      <div class="flex justify-center items-center">
        <workspace-icon :name="workspace.name" :src="workspaceImage"></workspace-icon>
        <div>
          <div class="flex items-center">
            <span class="uppercase font-bold text-light ">{{ workspaceName }}</span>
            <icon class="ml-2 w-3 h-3 fill-white" name="chevron-down"></icon>
          </div>
          <div>
            <div class="text-sm leading-4 text-left text-gray-light">{{ $t('Workspace') }}</div>
          </div>
        </div>
      </div>
      <div slot="dropdown">
        <div
          class="sm:mt-1 py-2 bg-white rounded-b-3xl rounded-t-none sm:rounded shadow drop-shadow-2xl overflow-y-auto max-h-screen-70"
        >
          <div v-for="item in $page.props.auth.user.data.workspaces" :key="item.id">
            <Link
              class="flex items-center px-6 py-2 hover:bg-light w-full text-left"
              :class="{'bg-light':isCurrent(item.id)}"
              :href="route('dashboard', { workspace: item.id })"
              as="button"
            >
              <workspace-icon :name="item.name" :src="item.image.url" />
              <div>
                <div class="text-dark">{{ item.name }}</div>
                <div class="text-gray text-sm">{{ item.plan }}</div>
              </div>
            </Link>
          </div>
          <div class="border-b mx-5 my-1" />
          <div class="block px-6 py-2 hover:bg-light text-left cursor-pointer"
               @click="showUpdateModal()"
          >
            {{ $t('Workspace settings') }}
          </div>
          <div class="border-b mx-5 my-1" />
          <div class="block px-6 hover:bg-light w-full text-left cursor-pointer"
               @click="showCreateModal()"
          >
            <icon class="w-4 h-4 fill-secondary inline my-2.5 mx-1.5" name="plus"></icon>
            <span class="content-center">{{ $t("Create") }}</span>
          </div>

          <!-- Invitations -->
          <div v-if="invitations.length">
            <p class="text-dark font-bold px-6 py-2 cursor-pointer bg-orange-opacity hover:bg-orange" @click="openInvitationModal()">
              {{ $t("Your invitations") }} <span>({{ invitations.length }})</span>
            </p>
          </div>
        </div>
        <div class="flex justify-center items-center p-3 sm:hidden">
          <icon class="fill-light" name="close"></icon>
        </div>
        <div style="z-index: -1" class="h-screen sm:h-auto relative opacity-30 -mt-51 sm:hidden bg-dark" />
      </div>
    </dropdown>
  </div>
</template>

<script>
import WorkspaceCreateModal from '@/Shared/Modals/WorkspaceCreateModal'
import WorkspaceUpdateModal from '@/Shared/Modals/WorkspaceUpdateModal'

import InvitationModal from '@/Shared/Modals/InvitationModal'

export default {
  components: {},
  computed: {
    workspace: function () {
      return this.$page
        .props
        .auth
        .user
        .data
        .workspaces
        .find(item => parseInt(item.id) === parseInt(this.$page.props.auth.workspace_id))
    },
    workspaceName: function () {
      let name = this.workspace.name

      if (name.length > 10) name = name.substring(0, 10) + '...'

      return name
    },
    invitations: function () {
      return  this.$page.props.auth.user.data.invitations
    },
    workspaceImage: function () {
      return this.$page
        .props
        .auth
        .user
        .data
        .workspaces
        .find(item => parseInt(item.id) === parseInt(this.$page.props.auth.workspace_id)).image.url
    },
  },
  methods: {
    isCurrent(id) {
      return parseInt(this.$page.props.auth.workspace_id) === parseInt(id)
    },
    showCreateModal() {
      this.$modal.show(WorkspaceCreateModal, {})
    },
    showUpdateModal() {
      this.$modal.show(WorkspaceUpdateModal, {'workspace': this.workspace})
    },
    openInvitationModal() {
      this.$modal.show(InvitationModal, {invitations: this.invitations},{ width:708 })
    },
  },
}
</script>
