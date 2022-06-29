<template>
  <div class="flex flex-col justify-between">
    <Head :title="$t('Sign up')" />
    <div class="flex-auto flex items-center justify-center pt-14">
      <div class="w-full max-w-md">
        <logo class="block mx-auto w-full max-w-xs fill-white" />
        <form class="auth-form mt-8 px-1 py-1 overflow-hidden mx-11 sm:mx-0" @submit.prevent="register">
          <h2 class="text-dark text-center font-bold text-xl sm:text-2xl">{{ $t('Create profile') }}</h2>

          <div class="text-dark mt-7" v-html="$t('Create profile from invitation message', {email: form.email})" />
          <text-input id="full-name"
                      v-model="form.full_name"
                      :error="form.errors.full_name"
                      class="mt-7"
                      :label="$t('First name')"
                      :placeholder="$t('Enter your full name')"
                      type="text"
                      autofocus autocapitalize="off"
          />
          <text-input id="password"
                      v-model="form.password"
                      :error="form.errors.password"
                      class="mt-3.5"
                      :label="$t('Password')"
                      :placeholder="$t('Enter your password')"
                      type="password"
          />
          <text-input id="password-confirmation"
                      v-model="form.password_confirmation"
                      :error="form.errors.password_confirmation"
                      class="mt-3.5"
                      :label="$t('Repeat Password')"
                      :placeholder="$t('Enter your password again')"
                      type="password"
          />
          <div class="flex justify-center mt-9">
            <loading-button :loading="form.processing" class="btn-cta-secondary" type="submit">
              {{ $t('Sign up') }}
            </loading-button>
          </div>
        </form>
      </div>
    </div>
    <div class="flex-none text-center py-10 sm:py-14 text-light">
      <span class="mr-2.5"> {{ $t('Already have an account?') }}</span>
      <Link class="text-secondary hover:underline" :href="route('login')">
        {{ $t('Sign in') }}
      </link>
    </div>
  </div>
</template>

<script>

import LoadingButton from '@/Shared/LoadingButton'
import LoginLayout from '@/Shared/Layouts/LoginLayout'

export default {
  components: {
    LoadingButton,
  },
  layout: LoginLayout,
  props: {
    invitation: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        email: this.invitation.data.email,
        full_name: '',
        password: '',
        password_confirmation: '',
        remember: false,
      }),
    }
  },
  methods: {
    register() {
      this.form.post(this.route('invitation.accept.guest', this.invitation.data.token))
    },
  },
}
</script>
