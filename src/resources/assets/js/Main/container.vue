<script>
import Forms from './forms'
import DefaultTab from './../Tabs/default'
import VendorTab from './../Tabs/vendor'

export default {
    components: {DefaultTab, VendorTab},
    name: 'lingo',
    mixins: [Forms],
    props: [
        'routes',
        'translations'
    ],
    data() {
        return {
            activeTab: 'default-tab',
            new_locale: null,
            new_file: null,
            new_vendor: null,
            localesList: [],
            filesList: [],
            dirsList: [],
            selectedKeyFormat: '',
            selectedFileName: '',
            selectedDirName: '',
            copiedItem: '',
            copyKeyFormat: [
                'trans(\'value\')',
                '__(\'value\')',
                '{!! trans(\'value\') !!}',
                '{!! __(\'value\') !!}',
                '{{ trans(\'value\') }}',
                '{{ __(\'value\') }}',
                '@lang(\'value\')'
            ]
        }
    },
    beforeMount() {
        this.preVisited()
    },
    computed: {
        localeExist() {
            return this.localesList && this.localesList.includes(this.new_locale) || false
        },
        fileExist() {
            return this.filesList && this.filesList.includes(this.new_file) || false
        },
        dirExist() {
            return this.dirsList && this.dirsList.includes(this.new_vendor) || false
        }
    },
    methods: {
        // local-storage
        getLs() {
            return this.$ls.get('ctf0-lingo', {})
        },
        setLs(obj) {
            return this.$ls.set('ctf0-lingo', obj)
        },
        preVisited() {
            let ls = this.getLs()

            if (Object.keys(ls).length) {
                this.activeTab = ls.tab
                this.selectedKeyFormat = ls.format
            } else {
                this.setLs({
                    tab: this.activeTab,
                    dir: this.selectedDirName,
                    file: this.selectedFileName,
                    format: this.selectedKeyFormat
                })
            }
        },

        // tabs
        activeTabIs(tab) {
            return this.activeTab == tab
        },
        toggleTab(tab) {
            this.activeTab = tab
        },

        // ph
        placeHolder(val, all = '') {
            return this.vendorCheck()
                ? `lang/vendor/${this.selectedDirName}/${all}${val}`
                : `lang/${all}${val}`
        },
        vendorPH(val) {
            return `lang/vendor/${val}`
        },

        // vendor
        vendorCheck() {
            return this.activeTabIs('vendor-tab') && this.selectedDirName
        },
        newVendor() {
            setTimeout(() => {
                return this.vendorCheck() && this.filesList.length == 0
            }, 250)
        },

        // utils
        updateLs(obj) {
            let oldLs = this.getLs()

            Object.assign(oldLs, obj)
            this.setLs(oldLs)
        },
        resetAll(items) {
            items.forEach((e) => {
                this[e] = ''
            })
        },
        trans(key) {
            return this.translations[key]
        },

        // notifs
        showNotif(msg, s = 'success', dur = null) {

            let title

            switch (s) {
                case 'black':
                case 'danger':
                    title = 'Error'
                    break
                case 'warning':
                    title = 'Warning'
                    dur = 2
                    break
                case 'link':
                    title = 'Info'
                    break
                default:
                    title = 'Success'
                    dur = 3
            }

            EventHub.fire('showNotif', {
                title: title,
                body: msg,
                type: s,
                duration: dur
            })
        },
        failedAjax() {
            this.showNotif(this.trans('ajax_error'), 'black')
        },
        missingVal(msg = null) {
            this.showNotif(msg || this.trans('no_val'), 'warning')
        }
    },
    watch: {
        activeTab(val) {
            this.updateLs({tab: val})
        },
        selectedDirName(val) {
            this.updateLs({dir: val})
        },
        selectedFileName(val) {
            this.updateLs({file: val})
        },
        selectedKeyFormat(val) {
            if (val == 'clear') {
                this.selectedKeyFormat = ''
            }

            this.updateLs({format: val})
        }
    },
    render() {}
}
</script>
