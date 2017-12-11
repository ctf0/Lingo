/*                Libs                */
window.Vue = require('vue')
window.EventHub = require('vuemit')
Vue.use(require('vue-tippy'))
Vue.use(require('vue-clipboard2'))
Vue.use(require('vue-ls'))

// axios
window.axios = require('axios')
axios.defaults.headers.common = {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'X-Requested-With': 'XMLHttpRequest'
}
// vue-awesome
import 'vue-awesome/icons/trash'
import 'vue-awesome/icons/search'
import 'vue-awesome/icons/archive'
import 'vue-awesome/icons/globe'
import 'vue-awesome/icons/qrcode'
import 'vue-awesome/icons/keyboard-o'
import 'vue-awesome/icons/file'
import 'vue-awesome/icons/file-o'
import 'vue-awesome/icons/files-o'
import 'vue-awesome/icons/folder'

Vue.component('icon', require('vue-awesome/components/Icon'))

/*                Components                */
Vue.component('Lingo', require('./Main/container'))
Vue.component('MyNotification', require('vue-notif'))

new Vue({
    el: '#app'
});