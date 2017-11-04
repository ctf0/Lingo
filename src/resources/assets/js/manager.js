$.ajaxSetup({
    cache: false,
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
})

/*                Libs                */
window.Vue = require('vue')
window.EventHub = require('vuemit')

/*                Components                */
Vue.component('Lingo', require('./Main/container'))
Vue.component('MyNotification', require('vue-notif'))
