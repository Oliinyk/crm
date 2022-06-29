<template>
  <div>
    <form-modal :form="form"
                :name="$t('Create Group of users')"
                @close="$emit('close')"
                @submit="store"
    >
      <text-input v-model="form.name" :error="form.errors.name" class="pb-7 w-full" :label="$t('Group Name')" />
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
  data() {
    return {
      form: this.$inertia.form({
        name: null,
      }),
    }
  },
  methods: {
    store() {
      this.form.post(this.route('group.store', {workspace: this.$page.props.auth.workspace_id}), {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
  },
}
</script>
