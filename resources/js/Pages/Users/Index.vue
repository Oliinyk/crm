<template>
  <div>
    <Head :title="$t('Users')" />
    <h1 class="mb-8 font-bold text-dark text-xl">{{ $t('Users') }}</h1>
    <div class="pb-5">
      <groups
        ref="groups"
        :groups="groups"
        :data="filters"
        :members="users.data"
        :can="can"
        class="flex justify-start sm:border-b text-dark"
      />
    </div>
    <div class="mb-6 flex flex-wrap justify-between items-center">
      <div class="flex">
        <button class="btn-cta-primary inline-flex font-semibold" @click="openInvitationModal()">
          <icon name="plus" class="w-6 h-6 mr-0.5 fill-light inline"></icon>
          <span>{{ $t('Invite') }}</span>
          <span class="hidden md:inline pl-1">{{ $t('user') }}</span>
        </button>
        <button class="text-secondary font-semibold px-5"
                :disabled="!invitations.length"
                @click="openPendingInvitationModal()"
        >
          <span>{{ $t('Pending invitation') }}</span>
          <span v-if="invitations.length">({{ invitations.length }})</span>
        </button>
      </div>
      <div class="sm:hidden" @click="openSearch()">
        <icon name="search" class="w-6 h-6 mr-2.5 fill-gray inline"></icon>
      </div>
      <div class="flex w-full sm:w-auto">
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
    <users-table :users="users" :show-checkboxes="false"></users-table>
  </div>
</template>

<script>
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layouts/Layout'
import throttle from 'lodash/throttle'
import Groups from '@/Pages/Groups/Partials/Groups'
import UsersTable from '@/Shared/Tables/UsersTable'
import InviteUserModal from '@/Shared/Modals/InviteUserModal'
import PendingInvitationModal from '@/Shared/Modals/PendingInvitationModal'

export default {
  components: {
    UsersTable,
    Groups,
  },
  layout: Layout,
  props: {
    filters: Object,
    users: Object,
    groups: Array,
    can: Object,
    invitations: Object,
  },
  data() {
    return {
      isCheckedAll: false,
      checkedItems: [],
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
        this.$inertia.get(this.route('user.index', {workspace: this.$page.props.auth.workspace_id}), pickBy(this.form), {preserveState: true})
      }, 150),
    },
  },
  methods: {
    openSearch() {
      return this.showSearch = !this.showSearch
    },
    openInvitationModal() {
      this.$modal.show(InviteUserModal)
    },
    openPendingInvitationModal() {
      this.$modal.show(PendingInvitationModal, {invitations: this.invitations}, {width: 708})
    },
  },
}
</script>
