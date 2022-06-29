<template>
  <div>
    <form-modal :form="form" :name="$t('Pending invitations')" :show-buttons="false" @close="$emit('close')">
      <div class="w-full">
        <pending-invitations-table :invitations="invitations" @close="$emit('close')" />
      </div>
    </form-modal>
  </div>
</template>

<script>

import FormModal from '@/Shared/Modals/FormModal'
import PendingInvitationsTable from '@/Shared/Tables/PendingInvitationsTable'

export default {
  name: 'LargeModal',
  components: {
    PendingInvitationsTable,
    FormModal,
  },
  props: {
    invitations: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
      }),
    }
  },
  methods: {
    //
  },
}
</script>
