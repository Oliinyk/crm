import Vue from 'vue'
import {InertiaProgress} from '@inertiajs/progress'
import {createInertiaApp} from '@inertiajs/inertia-vue'

import i18n from '@/Setup/i18n'
import '@/Setup/Components'
import '@/Setup/Mixines'
import '@/Setup/Plugins'
import '@/Setup/productionTips'

/**
 * Start progress bar.
 */
InertiaProgress.init()

createInertiaApp({
  resolve: async name => (await import(`./Pages/${name}`)),
  setup({el, app, props, plugin}) {
    Vue.use(plugin)
    new Vue({
      render: h => h(app, props),
      i18n,
    }).$mount(el)
  },
  title: title => (title ? `${title} - ${process.env.MIX_APP_NAME}` : process.env.MIX_APP_NAME),
})
