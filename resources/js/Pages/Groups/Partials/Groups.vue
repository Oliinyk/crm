<template>
  <div class="flex flex-auto justify-between">
    <div class="hidden sm:flex flex-wrap items-center justify-start">
      <Link class="px-4 py-2 cursor-pointer"
            :class="{ 'sm:border-b-3 border-secondary':groupId == null }"
            :href="route('user.index',{workspace: $page.props.auth.workspace_id})"
      >
        {{ $t('All Users') }}
      </Link>
      <Link v-for="group in groups" :key="group.id"
            :href="route('group.show',{'workspace':$page.props.auth.workspace_id,'group':group.id})"
            :data="filters"
            :class="groupId === parseInt(group.id)?'sm:border-b-3 border-secondary text-dark font-bold':'text-gray '"
            class="px-4 py-2 flex items-center justify-center cursor-pointer"
      >
        {{ group.name }}
      </Link>
    </div>
    <select id="select-1" v-model="selected"
            name="group-select" class="form-input block sm:hidden"
    >
      <option :value="null">{{ $t('All Users') }}</option>
      <option v-for="group in groups"
              :key="group.id"
              :value="group.id"
      >
        {{ group.name }}
      </option>
    </select>
    <div v-if="can.manage_groups"
         class="px-4 py-2 flex-none items-center text-secondary font-bold cursor-pointer sm:border-l"
         @click="showCreateModal"
    >
      <icon name="plus" class="w-6 h-6 mr-0.5 fill-secondary inline"></icon>
      {{ $t('New group') }}
    </div>
  </div>
</template>

<script>
import GroupCreateModal from '@/Shared/Modals/GroupCreateModal'
import pickBy from 'lodash/pickBy'

export default {
  components: {},
  props: {
    groups: Array,
    groupId: {
      type: Number,
      default: null,
    },
    members: Array,
    data: Object,
    can: Object,
  },
  data() {
    return {
      selected: this.groupId,
    }
  },
  computed: {
    filters() {
      return pickBy(this.data)
    },
  },
  watch: {
    selected: function (newSelect) {
      if (!newSelect) {
        this.$inertia.get(this.route('user.index', {workspace: this.$page.props.auth.workspace_id}))
        return
      }
      this.$inertia.get(this.route('group.show', {
        'workspace': this.$page.props.auth.workspace_id,
        'group': newSelect,
      }))
    },
  },
  created: function () {
  },
  methods: {
    showCreateModal() {
      this.$modal.show(GroupCreateModal)
    },
  },
}
</script>
