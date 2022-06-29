<template>
  <div>
    <Head :title="$t('Project')" />
    <div class="mb-8 flex justify-start max-w-3xl">
      <h1 class="font-bold text-xl">
        {{ form.name }}
      </h1>
    </div>
    <trashed-message v-if="project.deleted_at" class="mb-6" @restore="restore">
      {{ $t('This project has been deleted.') }}
    </trashed-message>
    <div class="bg-white rounded-md shadow overflow-hidden max-w-3xl">
      <form @submit.prevent="update">
        <div class="p-8 -mr-6 -mb-8 flex flex-wrap">
          <text-input v-model="form.name"
                      :error="form.errors.name"
                      class="pr-6 pb-8 w-full lg:w-1/2"
                      :label="$t('Name')"
          />
          <file-input v-model="form.photo"
                      :error="form.errors.photo"
                      class="pr-6 pb-8 w-full lg:w-1/2"
                      type="file"
                      accept="image/*"
                      :label="$t('Photo')"
          />
        </div>
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">
          <button class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">
            {{
              $t('Delete')
            }}
          </button>
          <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">
            {{
              $t('Update')
            }}
          </loading-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import ProjectLayout from '@/Shared/Layouts/ProjectLayout'
import LoadingButton from '@/Shared/LoadingButton'
import TrashedMessage from '@/Shared/Messages/TrashedMessage'

export default {
  components: {
    LoadingButton,
    TrashedMessage,
  },
  layout: ProjectLayout,
  props: {
    project: Object,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        _method: 'put',
        name: this.project.name,
        photo: this.project.photo,
      }),
    }
  },
  methods: {
    update() {
      this.form.post(this.route('project.update', {
        workspace: this.$page.props.auth.workspace_id,
        project: this.project.id,
      }))
    },
    destroy() {
      if (confirm(this.$t('Are you sure you want to delete this project?'))) {
        this.$inertia.delete(this.route('project.destroy', {
          workspace: this.$page.props.auth.workspace_id,
          project: this.project.id,
        }))
      }
    },
  },
}
</script>
