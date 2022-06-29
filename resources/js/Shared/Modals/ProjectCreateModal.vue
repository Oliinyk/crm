<template>
  <div>
    <form-modal :form="form" :name="$t('Create Project')" @submit="store" @close="$emit('close')">
      <text-input data-cy="create-project-name" v-model="form.name" :error="form.errors.name" class="pb-7 w-full" :label="$t('Name')" />
      <text-input data-cy="create-project-client" v-model="form.name" :error="form.errors.name" class="pb-7 w-full" :label="$t('Client')" />
      <text-input data-cy="create-project-lead" v-model="form.name" :error="form.errors.name" class="pb-7 w-full" :label="$t('Lead')" />
      <textarea-input data-cy="create-project-description" v-model="form.name" :error="form.errors.name" class="pb-7 w-full" :label="$t('Description')" />
      <text-input data-cy="create-project-address" v-model="form.name" :error="form.errors.name" class="pb-7 w-full" :label="$t('Address')" />
      <text-input data-cy="create-project-cost" v-model="form.name" :error="form.errors.name" class="pb-7 w-full" :label="$t('Cost')" />
      <text-input data-cy="create-project-working-hours" v-model="form.working_hours" :error="form.errors.working_hours" class="pb-7 w-full"
                  :label="$t('Working Hours')"
      />
    </form-modal>
  </div>
</template>

<script>
import TextareaInput from '@/Shared/Inputs/TextareaInput'
import FormModal from '@/Shared/Modals/FormModal'

export default {
  name: 'LargeModal',
  components: {
    FormModal,
    TextareaInput,
  },
  data() {
    return {
      form: this.$inertia.form({
        name: null,
        working_hours: '8',
      }),
    }
  },
  methods: {
    store() {
      this.form.post(this.route('project.store', {workspace: this.$page.props.auth.workspace_id}), {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
  },
}
</script>
