<template>
  <div v-if="hasModal()">
    <modal v-for="modal in modalComponents"
           :key="modal.id"
           :name="modal.id"
           height="auto"
           :adaptive="true"
           :scrollable="true"
           :reset="true"
           :width="modal.width?modal.width:492"
           @before-close="hide(modal.redirect)"
    >
      <Component v-bind="modal" :is="modal.component" @close="hide(modal.redirect)" />
    </modal>
  </div>
</template>

<script>

export default {
  computed: {
    modalComponents() {
      return this.$page.props.modals.map((modal) => {
        return {
          'component': modal.component,
          'id': modal.component,
          ...modal,
        }
      })
    },
  },
  watch: {
    '$page.props.modals': {
      handler() {
        if (this.hasModal()) {
          this.loadComponents()
        }
      },
      deep: true,
    },
  },
  mounted() {
    if (this.hasModal()) {
      this.loadComponents()
    }
  },
  methods: {
    hide(redirect) {
      this.$inertia.get(redirect)
    },
    loadComponents() {
      this.$nextTick(() => {
        this.modalComponents.forEach((modal => {
          this.$modal.show(modal.component, {...modal})
        }))
      })
    },
    hasModal() {
      if (!this.$page.props.modals) {
        return false
      }
      return this.$page.props.modals.length
    },
  },
}
</script>