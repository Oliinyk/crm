<template>
  <div>
    <form-modal :form="form"
                :name="$t('Update Role')"
                :submit-button-name="$t('Update')"
                :show-delete-button="!isMyRole && can.delete_roles"
                @submit="update"
                @close="$emit('close')"
                @delete="destroy"
    >
      <text-input id="update-role-name"
                  v-model="form.name"
                  :error="form.errors.name"
                  class="pb-8 w-full"
                  label="Name"
      />
      <div class="font-bold form-label w-full pb-2 text-dark border-t pt-5">{{ $t('Workspace permissions') }}</div>
      <div class="form-label w-full pb-1 text-dark">{{ $t('Projects') }}</div>
      <select-input v-model="form.see_projects"
                    class="mb-8 w-full"
                    :error="form.errors.see_projects"
                    :label="$t('See projects')"
                    :options="[
                      { label: $t('All'), value:'see all projects' },
                      { label: $t('Only where participating'), value:'see joined projects' }
                    ]"
                    data-cy="update-role-workspace-permissions-select"
      >
      </select-input>
      <checkbox-input v-model="form.create_projects"
                      :error="form.errors.create_projects"
                      class="pb-8 pl-2 w-full update-role-create-projects"
                      true-value="create projects"
                      :false-value="null"
                      :label="$t('Create projects')"
      />
      <checkbox-input v-model="form.edit_all_projects"
                      :error="form.errors.edit_all_projects"
                      class="pb-8 pl-2 w-full update-role-edit-projects"
                      true-value="edit all projects"
                      :false-value="null"
                      :label="$t('Edit Other User projects')"
      />
      <select-input v-model="form.delete_projects"
                    class="mb-8 w-full"
                    :error="form.errors.delete_projects"
                    :label="$t('Delete projects')"
                    :options="[
                      { label: $t('All'), value:'delete all projects' },
                      { label: $t('Only created by user'), value:'delete own projects' }
                    ]"
                    data-cy="update-role-delete-projects"
      >
      </select-input>
      <div class="form-label w-full pb-1 text-dark">{{ $t('People') }}</div>
      <checkbox-input v-model="form.manage_groups"
                      :error="form.errors.manage_groups"
                      class="pb-8 pl-2 w-full update-role-manage-groups"
                      true-value="manage groups"
                      :false-value="null"
                      :label="$t('Manage groups')"
      />
      <div class="form-label w-full pb-1 pl-2">{{ $t('Clients') }}</div>
      <checkbox-input v-model="form.see_clients"
                      :error="form.errors.see_clients"
                      class="pb-8 pl-2 w-full update-role-see-clients"
                      true-value="see clients"
                      :false-value="null"
                      :label="$t('See clients')"
      />
      <checkbox-input v-model="form.add_clients"
                      :error="form.errors.add_clients"
                      :disabled="disableAndUnselectCheckbox(!form.see_clients,'add_clients')"
                      class="pb-8 pl-2 w-full update-role-add-update-clients"
                      true-value="add clients"
                      :false-value="null"
                      :label="$t('Add and update clients')"
      />
      <checkbox-input v-model="form.delete_clients"
                      :error="form.errors.delete_clients"
                      :disabled="disableAndUnselectCheckbox(!form.see_clients,'delete_clients') || disableAndUnselectCheckbox(!form.add_clients,'delete_clients')"
                      class="pb-8 pl-2 w-full update-role-delete-clients"
                      true-value="delete clients"
                      :false-value="null"
                      :label="$t('Delete clients')"
      />
      <div class="form-label w-full pb-1 pl-2">{{ $t('Roles') }}</div>
      <checkbox-input v-model="form.see_roles"
                      :disabled="isMyRole"
                      :error="form.errors.see_roles"
                      class="pb-8 pl-2 w-full update-role-see-roles"
                      true-value="see roles"
                      :false-value="null"
                      :label="$t('See roles')"
      />
      <checkbox-input v-model="form.add_roles"
                      :error="form.errors.add_roles"
                      :disabled="isMyRole || disableAndUnselectCheckbox(!form.see_roles,'add_roles')"
                      class="pb-8 pl-2 w-full update-role-add-update-roles"
                      true-value="add roles"
                      :false-value="null"
                      :label="$t('Add and update roles')"
      />
      <checkbox-input v-model="form.delete_roles"
                      :error="form.errors.delete_roles"
                      :disabled="isMyRole || disableAndUnselectCheckbox(!form.see_roles,'delete_roles') || disableAndUnselectCheckbox(!form.add_roles,'delete_roles')"
                      class="pb-8 pl-2 w-full update-role-delete-roles"
                      true-value="delete roles"
                      :false-value="null"
                      :label="$t('Delete roles')"
      />
      <div class="form-label w-full pb-1 pl-2">{{ $t('Templates') }}</div>
      <checkbox-input v-model="form.manage_ticket_types"
                      :error="form.errors.manage_ticket_types"
                      class="pb-8 pl-2 w-full update-role-manage-ticket-types"
                      true-value="manage ticket types"
                      :false-value="null"
                      :label="$t('Manage ticket types')"
      />
      <div class="font-bold form-label w-full pb-2 text-dark border-t pt-5">{{ $t('Project permissions') }}</div>
      <div class="form-label w-full pb-1 text-dark">{{ $t('Tickets') }}</div>
      <select-input v-model="form.see_tickets"
                    class="mb-8 w-full"
                    :error="form.errors.see_tickets"
                    :label="$t('See tickets')"
                    :options="[
                      { label: $t('All'), value:'see all tickets' },
                      { label: $t('Only where participating'), value:'see joined tickets' }
                    ]"
                    data-cy="update-role-project-permissions"
      />
      <checkbox-input v-model="form.create_tickets"
                      :error="form.errors.create_tickets"
                      class="pb-8 pl-2 w-full update-role-create-tickets"
                      true-value="create tickets"
                      :false-value="null"
                      :label="$t('Create tickets')"
      />
      <select-input v-model="form.edit_all_tickets"
                    class="mb-8 w-full"
                    :error="form.errors.edit_all_tickets"
                    :label="$t('Edit tickets')"
                    :options="[
                      { label: $t('All'), value:'edit all tickets' },
                      { label: $t('Only where participating'), value:'edit assignee tickets' }
                    ]"
                    data-cy="update-role-edit-tickets"
      />
      <select-input v-model="form.delete_tickets"
                    class="mb-8 w-full"
                    :error="form.errors.delete_tickets"
                    :label="$t('Delete tickets')"
                    :options="[
                      { label: $t('All'), value:'delete all tickets' },
                      { label: $t('Only created by user'), value:'delete own tickets' }
                    ]"
                    data-cy="update-role-delete-tickets"
      >
      </select-input>
      <div class="form-label w-full pb-1 text-dark">{{ $t('Project users') }}</div>
      <checkbox-input v-model="form.manage_project_members"
                      :error="form.errors.manage_project_members"
                      class="pb-8 pl-2 w-full update-role-add-delete-users"
                      true-value="manage project members"
                      :false-value="null"
                      :label="$t('Add/delete users in project')"
      />
    </form-modal>
  </div>
</template>

<script>
import FormModal from '@/Shared/Modals/FormModal'

export default {
  components: {
    FormModal,
  },
  props: {
    role: Object,
    can: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        name: this.role.name,
        see_projects: this.role.see_projects,
        create_projects: this.role.create_projects,
        edit_all_projects: this.role.edit_all_projects,
        manage_groups: this.role.manage_groups,
        delete_projects: this.role.delete_projects,
        see_clients: this.role.see_clients,
        add_clients: this.role.add_clients,
        delete_clients: this.role.delete_clients,
        see_roles: this.role.see_roles,
        add_roles: this.role.add_roles,
        delete_roles: this.role.delete_roles,
        manage_ticket_types: this.role.manage_ticket_types,
        see_tickets: this.role.see_tickets,
        create_tickets: this.role.create_tickets,
        edit_all_tickets: this.role.edit_all_tickets,
        delete_tickets: this.role.delete_tickets,

        manage_project_members: this.role.manage_project_members,
      }),
    }
  },
  computed: {
    isMyRole() {
      return this.$page
        .props
        .auth
        .user
        .data
        .roles
        .some(role => parseInt(role.id) === parseInt(this.role.id))
    },
  },
  methods: {
    update() {
      this.form.put(this.route('role.update', {workspace: this.$page.props.auth.workspace_id, role: this.role.id}), {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
    disableAndUnselectCheckbox(disable, unselect) {
      if (disable) {
        this.$set(this.form, unselect, null)
      }
      return disable
    },
    destroy() {
      if (confirm(this.$t('Are you sure you want to delete this role?'))) {
        this.$inertia.delete(this.route('role.destroy', {
          workspace: this.$page.props.auth.workspace_id,
          role: this.role.id,
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
