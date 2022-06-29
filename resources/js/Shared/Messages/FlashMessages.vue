<template>
  <notifications position="bottom right">
    <template slot="body" slot-scope="{ item, close }">
      <div class="vue-notification vue-notification-template" :class="item.type">
        <div class=" flex justify-between">
          <p class="notification-title">
            {{ item.title }}
          </p>
          <div class="notification-icon-close" @click="close">
            <icon name="close" class="icon w-4 h-4 cursor-pointer"></icon>
          </div>
        </div>
        <div class="notification-content" v-html="item.text" />
      </div>
    </template>
  </notifications>
</template>

<script>

export default {
  watch: {
    '$page.props.flash': {
      handler() {
        this.showErrorMessage()
        this.showMessage('success')
        this.showMessage('warning')
        this.showMessage('info')
      },
      deep: true,
    },
  },
  created: function () {
    this.showMessage('success')
    this.showMessage('warning')
    this.showMessage('info')
  },
  methods: {
    showErrorMessage() {
      if (this.$page.props.flash.error) {
        this.$notify({
          title: this.$t('Error'),
          text: this.$page.props.flash.error,
          type: 'error',
          duration: 10000,
        })
      }
      const count = Object.keys(this.$page.props.errors).length
      if (count > 0) {
        let errors = Object.values(this.$page.props.errors)
        this.$notify({
          title: this.$t('Error'),
          text: errors.join('<br>'),
          type: 'error',
          duration: 10000,
        })
      }
    },
    showMessage(type) {
      if (this.$page.props.flash[type]) {
        this.$nextTick(() => {
          this.$notify({
            title: this.$t(type),
            text: this.$page.props.flash[type],
            type: type,
            duration: 10000,
          })
        })
      }
    },
  },
}
</script>

<style>

</style>
