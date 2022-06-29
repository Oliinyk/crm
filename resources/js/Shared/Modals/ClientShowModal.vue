<template>
  <div>
    <form-modal :form="form"
                :name="$t('Client details')"
                :show-buttons="false"
                @close="$emit('close')"
    >
      <text-line v-model="form.name" :error="form.errors.name" class="pb-7 w-full" :label="$t('Name')" />
      <text-line v-model="form.status" :error="form.errors.status" class="pb-7 w-full" :label="$t('Status')" />
      <text-line v-model="form.email" :error="form.errors.email" class="pb-7 w-full" :label="$t('Email')" />
      <text-line v-model="form.phone" :error="form.errors.phone" class="pb-7 w-full" :label="$t('Phone')" />
      <text-line v-model="form.city" :error="form.errors.city" class="pb-7 w-full" :label="$t('City')" />
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
    client: Object,
    can: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        name: this.client.name,
        status: this.client.status,
        email: this.client.email,
        phone: this.client.phone,
        city: this.client.city,
      }),
    }
  },
  methods: {
    store() {
      this.form.put(this.route('client.update', {
        workspace: this.$page.props.auth.workspace_id,
        client: this.client.id,
      }), {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
    destroy() {
      if (confirm(this.$t('Are you sure you want to delete this client?'))) {
        this.$inertia.delete(this.route('client.destroy', {
          workspace: this.$page.props.auth.workspace_id,
          client: this.client.id,
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
