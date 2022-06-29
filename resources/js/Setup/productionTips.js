import Vue from 'vue'

/**
 * Check if the current env is production.
 * @type {boolean}
 */
let isProduction = process.env.MIX_APP_ENV === 'production'


Vue.config.productionTip = !isProduction // must be FALSE on prod
Vue.config.devtools = !isProduction // must be FALSE on prod
Vue.config.debug = !isProduction // must be FALSE on prod
Vue.config.silent = isProduction // must be TRUE on prod
