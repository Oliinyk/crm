<template>
  <button type="button" @click="show = !show">
    <slot />
    <div v-if="show" to="dropdown">
      <div>
        <div :style="styles" />
        <div ref="dropdown" class="dropdown sm:mt-3 sm:min-w-330 absolute sm:top-14 right-0 w-full sm:w-auto" style="z-index: 99999;">
          <div @click.stop="(closeInClick && (show = !show))">
            <slot name="dropdown" />
          </div>
          <template v-if="showOverlay" @click.stop="(closeInClick && (show = !autoClose))">
            <div class="flex justify-center items-center p-3 sm:hidden">
              <icon class="fill-light" name="close"></icon>
            </div>
            <div style="z-index: -1"
                 class="h-screen sm:h-auto relative opacity-30 -mt-36 sm:hidden bg-dark"
            />
          </template>
        </div>
      </div>
    </div>
  </button>
</template>

<script>
export default {
  props: {
    showOverlay: {
      type: Boolean,
      default: true,
    },
    autoClose: {
      type: Boolean,
      default: true,
    },
    closeInClick:{
      type: Boolean,
      default: true,
    },
    styles: {
      type: Object,
      default: () => {
        return {
          'position': 'fixed',
          'top': 0,
          'right': 0,
          'left': 0,
          'bottom': 0,
          'z-index': 99998,
          'opacity': .2,
        }
      },
    },
  },
  data() {
    return {
      show: false,
    }
  },
  watch: {
    show(show) {
      if (!show) {
        this.$emit('close')
      }
      let body = document.querySelector('body')
      show ? body.classList.add('overflow-y-hidden') : body.classList.remove('overflow-y-hidden')
    },
  },
  mounted() {
    document.addEventListener('keydown', e => {
      if (e.keyCode === 27) {
        this.show = false
      }
    })
  },
}
</script>

<style scoped>
  @media (max-width: 639px) {
    .dropdown {
      top: 56px;
    }
  }
</style>
