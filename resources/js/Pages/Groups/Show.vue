<template>
  <div>
    <Head :title="$t('Groups')" />
    <h1 class="mb-8 font-bold text-dark text-xl">{{ $t('Groups') }}</h1>
    <div class="pb-2 sm:pb-5">
      <groups
        ref="groups"
        :groups="groups"
        :data="filters"
        :group-id="group.id"
        :members="users.data"
        :can="can"
        class="flex justify-start sm:border-b text-dark"
      />
    </div>
    <div class="pb-2 sm:pb-6 flex flex-wrap items-center" :class="can.manage_groups?'justify-between':'justify-end'">
      <div>
        <button v-if="can.manage_groups" class="btn-cta-primary inline-flex" @click="showAddUserToAGroupModal()">
          <icon name="plus" class="w-6 h-6 mr-0.5 fill-light inline"></icon>
          <span>{{ $t('Add') }}</span>&nbsp;
          <span class="hidden md:inline">{{ $t('User to group') }}</span>
        </button>
        <button v-if="selectedMembers.length && can.manage_groups" class="btn-outline inline-flex "
                @click="showDeleteUserModal"
        >
          <icon name="trash" class="w-6 h-6 mr-0.5 fill-dark inline"></icon>
          <span class="hidden md:inline">{{ $t('Delete from group') }} ({{ selectedMembers.length }})</span>
        </button>
      </div>

      <div class="flex justify-end">
        <div @click="openSearch()">
          <icon name="search" class="w-6 h-6 mr-2.5 fill-gray inline sm:hidden"></icon>
        </div>
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
    <users-table :users="users"
                 :show-checkboxes="can.manage_groups"
                 @check="(members)=>$set(this,'selectedMembers',members)"
    />
  </div>
</template>

<script>
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layouts/Layout'
import throttle from 'lodash/throttle'
import Groups from '@/Pages/Groups/Partials/Groups'
import GroupUpdateModal from '@/Shared/Modals/GroupUpdateModal'
import UsersTable from '@/Shared/Tables/UsersTable'
import AddUserToAGroupModal from '@/Shared/Modals/AddUserToAGroupModal'

export default {
  components: {
    Groups,
    UsersTable,
  },
  layout: Layout,
  props: {
    filters: Object,
    users: Object,
    groups: Array,
    group: Object,
    can: Object,
  },
  data() {
    return {
      isCheckedAll: false,
      checkedItems: [],
      form: {
        search: this.filters.search,
        trashed: this.filters.trashed,
      },
      selectedMembers: [],
      showSearch: false,
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get(this.route('group.show', {
          'workspace': this.$page.props.auth.workspace_id,
          'group': this.group.id,
        }), pickBy(this.form), {preserveState: true})
      }, 150),
    },
  },
  methods: {
    showUpdateGroupModal() {
      this.$modal.show(GroupUpdateModal, {'group': this.group})
    },
    showAddUserToAGroupModal() {
      this.$modal.show(
        AddUserToAGroupModal,
        {
          'group': this.group,
          'members': this.users.data,
        },
      )
    },
    showDeleteUserModal() {
      if (confirm('Are you sure you want to delete this user?')) {
        this.$inertia.post(this.route('group.member.destroy', {
          workspace: this.$page.props.auth.workspace_id,
          group: this.group.id,
        }), {
          members: this.selectedMembers.map(member => {
            return {'id': member}
          }),
        })
        this.selectedMembers.splice(0)
      }
    },
    openSearch() {
      return this.showSearch = !this.showSearch
    },
  },
}
</script>
