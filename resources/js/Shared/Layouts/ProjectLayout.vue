<template>
  <div>
    <layout>
      <template #project>
        <div v-if="showMenu" class="bg-dark z-1 opacity-30 absolute inset-0" @click="toggleProjectMenu" />
        <div v-if="showMenu"
             class="absolute left-2/4 transform -translate-x-2/4 cursor-pointer h-6 w-6 top-40 z-10"
             @click="toggleProjectMenu"
        >
          <icon name="close" class="fill-light"></icon>
        </div>
        <project-menu
          :class="[showMenu ? 'min-h-screen top-36 overflow-y-auto overflow-x-hidden pb-56' : 'max-h-14 ']"
          class="bg-light fixed bottom-0 w-full md:block flex-shrink-0 md:relative md:max-h-full md:w-56 md:overflow-y-auto mt-16"
          :show-menu="showMenu" @openProjectMenu="openProjectMenu"
          @closeProjectMenu="closeProjectMenu"
        ></project-menu>
      </template>
      <slot />
    </layout>
  </div>
</template>

<script>
import ProjectMenu from '@/Shared/Menues/ProjectMenu'
import Layout from './Layout'
import resolveConfig from 'tailwindcss/resolveConfig'
import tailwindConfig from '../../../../tailwind.config.js'

const fullConfig = resolveConfig(tailwindConfig)

export default {
  components: {
    Layout,
    ProjectMenu,
  },
  data() {
    return {
      showMenu: false,
    }
  },
  watch: {
    showMenu(val) {
      let body = document.querySelector('body')
      val ? body.classList.add('overflow-y-hidden') : body.classList.remove('overflow-y-hidden')
    },
  },
  mounted() {
    const md = +fullConfig.theme.screens.md.replace('px', '')
    window.addEventListener('resize', e => {
      if (document.body.clientWidth >= md) {
        this.showMenu = false
      }
    })
  },
  methods: {
    openProjectMenu(val) {
      this.showMenu = val
    },
    closeProjectMenu(val) {
      this.showMenu = val
    },
    toggleProjectMenu() {
      this.showMenu = !this.showMenu
    },
  },
}
</script>
