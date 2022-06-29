<template>
  <div
    :class="['pt-1 md:pt-3 cursor-pointer z-2 ', showMenu ? 'block rounded-t-2xl' : 'flex flex-wrap flex-row-reverse justify-between max-h-14 px-3']"
    @click="openProjectMenu"
  >
    <div class="md:mb-4 flex md:block pr-6 pl-6 relative"
         :class="[{ 'pointer-events-none md:pointer-events-auto w-6/12 md:w-full': !showMenu }]"
    >
      <icon name="project-icon" class="fill-gray absolute top-2/4 transform -translate-y-2/4"></icon>
      <Link class="flex items-center group py-3 pl-7 overflow-hidden md:overflow-visible" :href="route('user.index', {workspace: $page.props.auth.workspace_id})">
        <span 
          class="text-gray font-semibold"
          :class="{'truncate md:whitespace-normal': !showMenu}"
        >
          {{ $page.props.project.name }}
        </span>
      </Link>
    </div>
    <div v-for="menuItem in projectMenuFormated"
         :key="menuItem.name"
         :class="{ 'hidden': showMenu }"
         class="flex items-center w-6/12 md:hidden"
    >
      <p class="py-3 pl-6 font-bold">{{ menuItem.name }}</p>
      <div class="p-3">
        <icon name="chevron-up" class="fill-gray"></icon>
      </div>
    </div>
    <div class="project-list w-full" :class="[{ 'pointer-events-none mt-1 md:pointer-events-auto': !showMenu }]"
         @click.stop="closeProjectMenu"
    >
      <div>
        <Link v-for="menuItem in $page.props.auth.projectMenu"
              :key="menuItem.name"
              class="flex items-center group py-3 pl-6"
              :href="menuItem.link"
              :class="{'bg-white md:bg-transparent': menuItem.isActive}"
        >
          <div :class="{'font-bold': menuItem.isActive}">{{ menuItem.name }}</div>
        </Link>
      </div>
    </div>
  </div>
</template>

<script>
import resolveConfig from 'tailwindcss/resolveConfig'
import tailwindConfig from '@/../../tailwind.config.js'

export default {
  components: {},
  props: {
    showMenu: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    projectMenuFormated() {
      return this.$page.props.auth.projectMenu.filter(item=>item.isActive)
    },
  },
  methods: {
    openProjectMenu() {
      let md = resolveConfig(tailwindConfig).theme.screens.md.replace('px', '')

      if (document.documentElement.clientWidth < md) {
        this.$emit('openProjectMenu', true)
      }
    },
    closeProjectMenu() {
      this.$emit('closeProjectMenu', false)
    },
  },
}
</script>
