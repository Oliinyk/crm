<template>
  <div>
    <form-modal :form="form" :name="$t('You are invited to these Workspaces')" :show-buttons="false" @close="$emit('close')">
      <div class="w-full">
        <invitations-table :invitations="invitations" @close="$emit('close')" />
      </div>
    </form-modal>
  </div>
</template>

<script>

import FormModal from '@/Shared/Modals/FormModal'
import InvitationsTable from '@/Shared/Tables/InvitationsTable'

export default {
  name: 'LargeModal',
  components: {
    FormModal,
    InvitationsTable,
  },
  props: {
    invitations: Array,
  },
  data() {
    return {
      form: null,
    }
  },
  methods: {
    //
  },
}
</script>
