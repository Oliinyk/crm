<template>
  <div>
    <Head :title="$t('Roles')" />
    <h1 class="mb-8 font-bold text-xl">{{ $t('Roles') }}</h1>
    <div class="flex justify-between items-center">
      <button v-if="can.add_roles" class="btn-cta-primary inline-flex" @click="showCreateModal()">
        <icon name="plus" class="w-6 h-6 mr-0.5 fill-light inline"></icon>
        <span>{{ $t("Create") }}</span>&nbsp;
        <span class="hidden md:inline">{{ $t("Role") }}</span>
      </button>
    </div>
    <roles-table :roles="roles" :can="can" />
  </div>
</template>

<script>
import Layout from '@/Shared/Layouts/Layout'
import RolesTable from '@/Shared/Tables/RolesTable'
import RoleCreateModal from '@/Shared/Modals/RoleCreateModal'

export default {
  components: {
    RolesTable,
  },
  layout: Layout,
  props: {
    roles: Object,
    can: Object,
  },
  methods: {
    showCreateModal() {
      this.$modal.show(RoleCreateModal, {})
    },
  },
}
</script>
