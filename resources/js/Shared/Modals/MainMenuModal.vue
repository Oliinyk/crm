<template>
  <div class="h-full overflow-y-auto">
    <div class="md:flex md:flex-shrink-0 bg-dark border-b border-white">
      <div class="md:flex-shrink-0 flex items-center justify-between md:justify-center h-16 pointer-events-none">
        <workspaces-dropdown />
        <div class="md:hidden mr-7 flex items-center">
          <user-icon
            size="small"
            :full-name="$page.props.auth.user.data.full_name"
            :src="$page.props.auth.user.data.image.url"
            class="mr-3"
          />
          <div class="pointer-events-auto" @click="$emit('close')">
            <icon name="close" class="h-10 sm:h-20 fill-light" />
          </div>
        </div>
      </div>
    </div>
    <div class="mx-7">
      <div>
        <div v-for="menuItem in $page.props.auth.topMenu"
             :key="menuItem.name"
             :class="menuItem.isActive?'text-light text-4xl font-bold':'text-gray'"
        >
          <div class="my-5 flex flex-col ml-4">
            <div class="flex items-center" @click="selectItem(menuItem)">
              <div class="text-xl text-white font-bold">{{ menuItem.name }}</div>
              <icon v-if="menuItem.children.length > 0" name="chevron-down"
                    :class="{ 'transform rotate-180': menuItem.name === selectedName }"
                    class="ml-4 w-4 h-4 fill-white inline"
              />
            </div>

            <div v-for="selectedItem in selectedItems.children" :key="selectedItem.name" class="text-gray">
              <div v-if="menuItem.name === selectedName">
                <div v-if="selectedItem.separator" class="border-b mt-4  border-gray my-1" />
                <div v-else-if="selectedItem.createProjectModal"
                     class="block ml-4 mt-4 hover:bg-light text-white font-bold text-lg"
                     @click="showCreateModal"
                >
                  {{ selectedItem.name }}
                </div>
                <div v-else class="ml-6 mt-4 flex justify-between text-lg text-white font-bold"
                     @click="navigate(selectedItem.link)"
                >
                  <div class="">{{ selectedItem.name }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ProjectCreateModal from '@/Shared/Modals/ProjectCreateModal'

export default {
  components: {},
  data() {
    return {
      selectedItems: {},
      selectedName: '',
    }
  },
  methods: {
    selectItem(item) {
      if (this.selectedName === item.name) {
        this.selectedItems = {}
        this.selectedName = ''
      } else {
        this.selectedName = item.name
        this.$set(this, 'selectedItems', item)

        if (!item.checkUrlName && item.routeName) {
          this.navigate(item.routeName, item.routeId)
        }
      }
    },
    showCreateModal() {
      this.$modal.show(ProjectCreateModal)
    },
    back() {
      this.$set(this, 'selectedItems', [])
    },
    navigate(link) {
      this.$inertia.get(link, {}, {
        onSuccess: () => {
          this.$emit('close')
        },
      })
    },
  },
}
</script>
