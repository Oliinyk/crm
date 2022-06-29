<template>
  <div class="flex flex-col justify-between h-screen">
    <Head :title="$t('Reset password')" />
    <div class="flex-auto flex items-center justify-center pt-14">
      <div class="w-full max-w-md">
        <logo class="block mx-auto w-full max-w-xs fill-white" />
        <form class="auth-form mt-8 px-1 py-1 overflow-hidden mx-11 sm:mx-0" @submit.prevent="login">
          <h2 class="text-dark text-center font-bold text-xl sm:text-2xl">{{ $t('Reset password') }}</h2>
          <text-input v-model="form.email"
                      :error="form.errors.email"
                      class="mt-7 "
                      :label="$t('Email')"
                      :placeholder="$t('Enter your e-mail')"
                      type="email"
                      autofocus autocapitalize="off"
          />
          <div class="flex justify-center mt-9">
            <loading-button :loading="form.processing" class="btn-cta-secondary" type="submit">
              {{ $t('Send Password Reset Link') }}
            </loading-button>
          </div>
        </form>
      </div>
    </div>
    <div class="flex-none text-center py-10 sm:py-14 text-light">
      <span class="mr-2.5"> {{ $t('Don`t have an account?') }}</span>
      <Link class="text-secondary hover:underline" :href="route('register')">
      {{ $t('Sign up') }}
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
  data() {
    return {
      form: this.$inertia.form({
        email: 'johndoe@example.com',
      }),
    }
  },
  methods: {
    login() {
      this.form.post(this.route('password.email'))
    },
  },
}
</script>
