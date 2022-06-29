import Vue from 'vue'
import PortalVue from 'portal-vue'
import VueMeta from 'vue-meta'
import Notifications from 'vue-notification'
import VModal from 'vue-js-modal'
import {fieldTypes} from '@/Setup/TicketTypeFields'
import Echo from 'laravel-echo'

window.Pusher = require('pusher-js')

/**
 * Setup plugins.
 */
Vue.prototype.$fieldTypes = fieldTypes
Vue.use(PortalVue)
Vue.use(VueMeta)
Vue.use(Notifications)
Vue.use(VModal, {
  dynamicDefaults: {
    classes: ['modal-override', 'modal-override1'],
    height: 'auto',
    adaptive: true,
    scrollable: true,
    width: '492px',
  },
})

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: process.env.MIX_PUSHER_APP_KEY,
  wsHost: window.location.hostname,
  wsPort: 6001,
  wssPort: 6001,
  forceTLS: false,
  disableStats: true,
  reconnectionAttempts: 5,
})
