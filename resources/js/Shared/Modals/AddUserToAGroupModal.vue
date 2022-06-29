<template>
  <div>
    <form-modal :form="selectedMembersForm"
                :name="$t('Add User to Group')"
                :submit-button-name="$t('Add')"
                @close="$emit('close')"
                @submit="store"
    >
      <p class="form-label">{{ $t('User email') }}:</p>
      <vselect-search
        v-model="selectedMembersForm.members"
        class="mb-8 w-full v-user"
        :relative-list="true"
        :multiple="true"
        :user-image="true"
        :close-on-select="false"
        :error="selectedMembersForm.errors.members"
        :url="route('group.index',{workspace: $page.props.auth.workspace_id, group:group.id})"
      />
    </form-modal>
  </div>
</template>

<script>
import FormModal from '@/Shared/Modals/FormModal'

export default {
  name: 'LargeModal',
  components: {
    FormModal,
  },
  props: {
    group: Object,
    members: Array,
  },
  data() {
    return {
      selectedMembersForm: this.$inertia.form({
        members: [],
        processing: false,
      }),
    }
  },
  methods: {
    store() {
      this.selectedMembersForm.post(this.route('group.member.store', {
        workspace: this.$page.props.auth.workspace_id,
        group: this.group,
      }), {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
  },
}
</script>
