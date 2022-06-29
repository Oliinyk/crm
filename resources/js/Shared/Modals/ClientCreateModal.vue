<template>
  <div>
    <form-modal :form="form" :name="$t('Create Client')" @submit="store" @close="$emit('close')">
      <text-input id="client-name" v-model="form.name" :error="form.errors.name" class="pb-7 w-full" :label="$t('Name')" />
      <text-input id="client-status" v-model="form.status" :error="form.errors.status" class="pb-7 w-full" :label="$t('Status')" />
      <text-input id="client-email" v-model="form.email" :error="form.errors.email" class="pb-7 w-full" :label="$t('Email')" />
      <text-input id="client-phone" v-model="form.phone" :error="form.errors.phone" class="pb-7 w-full" :label="$t('Phone')" />
      <text-input id="client-city" v-model="form.city" :error="form.errors.city" class="pb-7 w-full" :label="$t('City')" />
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
        status: null,
        email: null,
        phone: null,
        city: null,
      }),
    }
  },
  methods: {
    store() {
      this.form.post(this.route('client.store', {workspace: this.$page.props.auth.workspace_id}), {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
  },
}
</script>
