<template>
  <div>
    <form-modal :form="form"
                :name="$t('Show User')"
                :show-buttons="false"
                @submit="$emit('close')"
    >
      <user-icon
        :full-name="form.full_name"
        :src="form.image.url"
        class="pb-8"
        size="big"
      />
      <text-line v-model="form.full_name"
                 :error="form.errors.full_name"
                 class="pb-8 w-full"
                 label="First name"
      />
      <text-line v-model="form.email"
                 :error="form.errors.email"
                 class="pb-8 w-full"
                 label="Email"
      />
    </form-modal>
  </div>
</template>

<script>
import FormModal from '@/Shared/Modals/FormModal'

export default {
  name: 'WorkspaceUpdateModal',
  components: {
    FormModal,
  },
  props: {
    user: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        _method: 'put',
        full_name: this.user.full_name,
        image: this.user.image,
        email: this.user.email,
        password: this.user.password,
      }),
    }
  },
}
</script>
