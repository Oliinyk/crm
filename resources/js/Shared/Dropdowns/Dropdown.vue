<template>
  <button type="button" @click="show = true">
    <slot />
    <portal v-if="show" to="dropdown">
      <div>
        <div :style="styles" @click="show = false" />
        <div ref="dropdown" class="sm:mt-3 absolute z-99999" @click.stop="closeInClick && (show = !autoClose)">
          <slot name="dropdown" />
          <template v-if="showOverlay">
            <div class="flex justify-center items-center p-3 sm:hidden">
              <icon class="fill-light" name="close"></icon>
            </div>
            <div style="z-index: -1"
                 class="h-screen sm:h-auto relative opacity-30 -mt-36 sm:hidden bg-dark"
            />
          </template>
        </div>
      </div>
    </portal>
  </button>
</template>

<script>
import Popper from 'popper.js'

export default {
  props: {
    placement: {
      type: String,
      default: 'bottom-end',
    },
    showOverlay: {
      type: Boolean,
      default: true,
    },
    boundary: {
      type: String,
      default: 'scrollParent',
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
      this.$emit('show', show)
      if (show) {
        this.$nextTick(() => {
          // Custom modifier
          function fixedWidth(data) {
            const mediaQuery = window.matchMedia('(max-width: 639px)')
            if (!mediaQuery.matches) {
              return data
            }

            let top = document.documentElement.getBoundingClientRect().top
            if (top < 0) {
              top = Math.abs(top)
            }

            const newData = data
            newData.offsets.popper.left = 0
            newData.offsets.popper.top = top + 56
            newData.styles.width = document.documentElement.clientWidth
            newData.styles.maxWidth = document.documentElement.clientWidth
            return newData
          }

          this.popper = new Popper(this.$el, this.$refs.dropdown, {
            placement: this.placement,
            modifiers: {
              preventOverflow: {boundariesElement: this.boundary},
              fixedWidth: {
                enabled: true,
                fn: fixedWidth,
                order: 840,
              },
            },
          })
        })
      } else if (this.popper) {
        setTimeout(() => this.popper.destroy(), 100)
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
