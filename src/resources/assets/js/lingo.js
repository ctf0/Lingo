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
Vue.component('Lingo', require(`./${process.env.MIX_LINGO_FRAMEWORK}/lingo`))
Vue.component('MyNotification', require('vue-notif'))

