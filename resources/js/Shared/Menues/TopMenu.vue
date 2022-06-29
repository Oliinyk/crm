<template>
  <div class="flex">
    <top-menu-item v-for="menuItem in $page.props.auth.topMenu"
                   :key="menuItem.name"
                   :name="menuItem.name"
                   :children="menuItem.children"
                   :is-active="menuItem.isActive"
                   :link="menuItem.link"
    />
  </div>
</template>

<script>
import TopMenuItem from '@/Shared/Menues/TopMenuItem'

export default {
  components: {
    TopMenuItem,
  },
}
</script>
