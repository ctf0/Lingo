/*                Libs                */
window.Vue = require('vue')
window.EventHub = require('vuemit')
Vue.use(require('vue-tippy'))
Vue.use(require('vue-clipboard2'))
Vue.use(require('vue-ls'))

window.axios = require('axios')
axios.defaults.headers.common = {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'X-Requested-With': 'XMLHttpRequest'
}

/*                Components                */
Vue.component('Lingo', require('./Main/container'))
Vue.component('MyNotification', require('vue-notif'))
