<template>
  <div>
    <Head :title="$t('Projects')" />
    <h1 class="mb-8 font-bold text-dark text-xl">{{ $t('Projects') }}</h1>
    <div class="pb-2 sm:pb-7 flex flex-wrap justify-between items-center">
      <button v-if="can.create_project" class="btn-cta-primary flex" @click="showCreateModal()">
        <icon name="plus" class="w-6 h-6 mr-2.5 fill-light inline"></icon>
        <span>{{ $t('Create') }}</span>&nbsp;
        <span class="hidden md:inline">{{ $t('Project') }}</span>
      </button>
      <div @click="openSearch()">
        <icon name="search" class="w-6 h-6 mr-2.5 fill-gray inline sm:hidden"></icon>
      </div>
      <text-input v-model="form.search"
                  class="relative w-full max-w-md my-0 mt-2 sm:my-1.5"
                  :class="showSearch?'inline sm:hidden':' hidden sm:inline mr-4'"
                  autocomplete="off"
                  type="text"
                  name="search"
                  :placeholder="$t('Search…')"
      />
    </div>
    <projects-table :projects="projects"
                    :show-checkboxes="true"
                    @check="check"
    />
  </div>
</template>

<script>
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layouts/Layout'
import throttle from 'lodash/throttle'
import mapValues from 'lodash/mapValues'
import ProjectCreateModal from '@/Shared/Modals/ProjectCreateModal'
import ProjectsTable from '@/Shared/Tables/ProjectsTable'

export default {
  components: {
    ProjectsTable,
  },
  layout: Layout,
  props: {
    filters: Object,
    projects: Object,
    can: Object,
  },
  data() {
    return {
      form: {
        search: this.filters.search,
        trashed: this.filters.trashed,
      },
      showSearch: false,
      selectedProjects: [],
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get(this.route('project.index', {workspace: this.$page.props.auth.workspace_id}), pickBy(this.form), {preserveState: true})
      }, 150),
    },
  },
  methods: {
    reset() {
      this.form = mapValues(this.form, () => null)
    },
    showCreateModal(role) {
      this.$modal.show(ProjectCreateModal, {role})
    },
    openSearch() {
      return this.showSearch = !this.showSearch
    },
    check(selectedProjects) {
      this.$set(this, 'selectedProjects', selectedProjects)
    },
  },
}
</script>
