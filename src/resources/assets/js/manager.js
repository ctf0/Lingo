/*                Libs                */
window.Vue = require('vue')
window.EventHub = require('vuemit')
Vue.use(require('vue-clipboard2'))
Vue.use(require('vue-ls'))

// vue-tippy
Vue.use(require('vue-tippy'), {
    touchHold: true,
    inertia: true,
    performance: true,
    flipDuration: 0,
    popperOptions: {
        modifiers: {
            preventOverflow: {
                enabled: false
            },
            hide: {
                enabled: false
            }
        }
    }
})

// axios
window.axios = require('axios')
axios.defaults.headers.common = {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'X-Requested-With': 'XMLHttpRequest'
}
axios.interceptors.response.use((response) => {return response}, (error) => {return Promise.reject(error.response)})

// vue-awesome
import 'vue-awesome/icons/trash'
import 'vue-awesome/icons/archive'
import 'vue-awesome/icons/globe'
import 'vue-awesome/icons/qrcode'
import 'vue-awesome/icons/keyboard-o'
import 'vue-awesome/icons/file'
import 'vue-awesome/icons/file-o'
import 'vue-awesome/icons/files-o'
import 'vue-awesome/icons/folder'
import 'vue-awesome/icons/search'
import 'vue-awesome/icons/times'
import 'vue-awesome/icons/clone'
Vue.component('icon', require('vue-awesome/components/Icon'))

/*                Components                */
Vue.component('Lingo', require('./Main/container'))
Vue.component('MyNotification', require('vue-notif'))
