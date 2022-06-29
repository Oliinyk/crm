<template>
  <div>
    <form-modal :form="form"
                :name="$t('Update User')"
                :submit-button-name="$t('Update')"
                @submit="update"
                @close="$emit('close')"
    >
      <text-input id="full-name"
                  v-model="form.full_name"
                  :error="form.errors.full_name"
                  class="pb-8 w-full"
                  label="First name"
      />
      <text-input id="full-email"
                  v-model="form.email"
                  :error="form.errors.email"
                  class="pb-8 w-full"
                  label="Email"
      />
      <file-input v-model="form.image"
                  :error="form.errors.image"
                  class="pb-8 w-full"
                  type="file"
                  accept="image/*"
                  :extensions="['jpg', 'png', 'jpeg']"
                  label="Photo"
                  @processing="data => form.processing = data"
      />
      <text-input id="profile-password"
                  v-model="form.password"
                  :error="form.errors.password"
                  class="pb-8 w-full"
                  type="password"
                  autocomplete="new-password" label="Password"
      />
      <p class="form-label">{{ $t('locale') }}:</p>
      <v-select
        v-model="form.locale"
        class="mb-8 w-full form-select pl-0.5 py-0.5 flex items-center"
        :error="form.errors.locale"
        :options="[ 'en', 'ru']"
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
        full_name: this.user.data.full_name,
        image: this.user.data.image,
        email: this.user.data.email,
        password: this.user.data.password,
        locale: this.user.data.locale,
      }),
    }
  },
  methods: {
    update() {
      this.form.post(this.route('user.update',{workspace: this.$page.props.auth.workspace_id, user: this.user.data.id}), {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
  },
}
</script>
