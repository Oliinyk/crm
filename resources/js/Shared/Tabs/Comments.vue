<template>
  <div>
    <form @submit.prevent="store">
      <div class="flex">
        <user-icon
          :full-name="$page.props.auth.user.data.full_name"
          :src="$page.props.auth.user.data.image.url"
        />
        <textarea-input id="comment"
                        v-model="form.text"
                        class="pb-7 w-full rounded"
                        :placeholder="$t('Add a comment')"
                        :error="$page.props.errors.text"
        />
      </div>
      <div class="flex justify-end">
        <loading-button :loading="form.processing"
                        class="btn-accent"
                        type="submit"
        >
          {{ $t('Submit') }}
        </loading-button>
      </div>
    </form>
    <div v-for="comment in ticket.comments" :key="'comment-'+ comment.id" class="pb-7 flex">
      <user-icon :full-name="comment.author.full_name"
                 :src="comment.author.image.url"
      />
      <div>
        <p class="mt-1.5 mb-2">
          {{ comment.author.full_name }}
          <span class="text-gray">{{ comment.created_at }}</span>
        </p>
        <p>{{ comment.text }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import LoadingButton from '@/Shared/LoadingButton'

export default {
  components: {
    LoadingButton,
  },
  props: {
    ticket: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        text: null,
      }),
    }
  },
  methods: {
    store() {
      let route = this.route('ticket.comment.store', {
        workspace: this.$page.props.auth.workspace_id,
        ticket: this.ticket.id,
        project: this.$page.props.project.id,
      })

      this.form.post(route, {
        preserveScroll: true,
        onSuccess: () => {
          this.form.reset()
        },
      })
    },
  },
}
</script>
