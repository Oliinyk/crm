<template>
  <div class="pl-7">
    <div v-if="children.length">
      <dropdown v-if="children.length"
                placement="bottom"
                class="flex items-center group pb-4 pt-5 px-2"
                :class="{'border-b-3 border-secondary':isActive}"
      >
        <span :class="[isActive ? 'text-black font-bold' : 'text-gray' ]">{{ name }}</span>
        <icon :class="['w-3 h-3 ml-1 mt-1', isActive ? 'fill-black' : 'fill-gray']"
              name="chevron-down"
        />
        <div slot="dropdown" class="sm:-mt-2 mt-2 py-2 shadow bg-white text-dark rounded">
          <div v-for="child in children" :key="child.name">
            <div v-if="child.separator" class="border-b mx-5 my-1" />
            <div v-else-if="child.createProjectModal"
                 class="block px-6 py-2 hover:bg-light cursor-pointer"
                 @click="showCreateModal"
            >
              {{ child.name }}
            </div>
            <Link v-else class="block px-6 py-2 hover:bg-light"
                  :class="{'bg-light':child.isActive}"
                  :href="child.link"
            >
              {{ child.name }}
            </Link>
          </div>
        </div>
      </dropdown>
    </div>
    <div v-else>
      <Link class="flex items-center group pb-4 pt-5"
            :class="{'border-b-3 border-secondary':isActive }"
            :href="link"
      >
        <span class="px-2"
              :class="isActive ? 'text-black font-bold' : 'text-gray'"
        >
          {{ name }}
        </span>
      </Link>
    </div>
  </div>
</template>

<script>
import ProjectCreateModal from '@/Shared/Modals/ProjectCreateModal'

export default {
  props: {
    link: {
      type: String,
    },
    name: {
      type: String,
      default: '',
    },
    children: {
      type: Array,
      default: () => [],
    },
    isActive: {
      type: Boolean,
    },
  },
  methods: {
    showCreateModal() {
      this.$modal.show(ProjectCreateModal)
    },
  },

}
</script>
