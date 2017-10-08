<script>
import Ops from './../mixins/main-forms'
import DefaultTab from './default'
import VendorTab from './vendor'

export default {
    components: {DefaultTab, VendorTab},
    name: 'lingo',
    mixins: [Ops],
    props: [
        'currentLocale',
        'scanForMissingRoute',
        'addNewLocaleRoute',
        'addNewFileRoute',
        'addNewVendorRoute',
        'filesRoute',
        'selectedFileDataRoute',
        'deleteFileRoute',
        'deleteLocaleRoute',
        'saveFileRoute'
    ],
    data() {
        return {
            activeTab: 'default-tab',
            lingoTrans: '',
            new_locale: null,
            new_file: null,
            new_vendor: null,
            localesList: [],
            filesList: [],
            dirsList: [],
            selectedDirName: '',
            selectedFileName: ''
        }
    },
    computed: {
        localeExist() {
            return this.localesList && this.localesList.includes(this.new_locale)
        },
        fileExist() {
            return this.filesList && this.filesList.includes(this.new_file)
        },
        dirExist() {
            return this.dirsList && this.dirsList.includes(this.new_vendor)
        }
    },
    mounted() {
        this.preVisited()
        this.getLingoTrans()
        this.updateDataEvents()
    },
    updated() {
        this.reflowTable()
    },
    methods: {
        // main
        preVisited() {
            let ls = JSON.parse(localStorage.getItem('lingo'))
            if (ls) {
                this.activeTab = ls.tab

                setTimeout(() => {
                    EventHub.fire('ls-dir', ls.dir)
                    EventHub.fire('ls-file', {
                        tab: ls.tab,
                        val: ls.file
                    })
                }, 50)

            } else {
                localStorage.setItem('lingo', JSON.stringify({
                    tab: this.activeTab,
                    dir: this.selectedDirName,
                    file: this.selectedFileName
                }))
            }
        },
        getLingoTrans() {
            $.post(this.selectedFileDataRoute, {
                'file_name': 'messages.php',
                'dir_name': 'Lingo'
            }, (data) => {
                if (data.success) {
                    this.lingoTrans = data.message.all
                }
            }).fail(() => {
                this.failedAjax()
            })
        },
        updateDataEvents() {
            EventHub.listen('locales_fetched', (data) => {
                if (data.tab == this.activeTab) {
                    this.localesList = data.val
                }
            })

            EventHub.listen('files_fetched', (data) => {
                if (data.tab == this.activeTab) {
                    this.filesList = data.val
                }
            })

            EventHub.listen('dirs_fetched', (val) => {
                this.dirsList = val
            })

            EventHub.listen('dir_got_selected', (val) => {
                this.selectedDirName = val
            })

            EventHub.listen('file_got_selected', (data) => {
                if (data.tab == this.activeTab) {
                    this.selectedFileName = data.val
                }
            })
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
            return this.vendorCheck() && this.filesList.length == 0
        },

        // utils
        updateLs(obj) {
            let oldLs = JSON.parse(localStorage.getItem('lingo')) || {}

            oldLs[Object.keys(obj)[0]] = Object.values(obj)[0]

            localStorage.setItem('lingo', JSON.stringify(oldLs))
        },
        resetAll(items) {
            items.forEach((e) => {
                this[e] = ''
            })
        },
        reflowTable() {
            setTimeout(() => {
                $('table').trigger('reflow')
            }, 10)
        },

        // notifs
        showNotif(msg, s = 'success') {

            let title = ''
            let duration = null

            switch (s) {
            case 'danger':
            case 'black':
                title = 'Error'
                break
            case 'warning':
                title = 'Warning'
                duration = 2
                break
            default:
                title = 'Success'
                duration = 3
            }

            EventHub.fire('showNotif', {
                title: title,
                body: msg,
                type: s,
                duration: duration
            })
        },
        failedAjax() {
            let msg = this.lingoTrans.ajax_fail[this.currentLocale] || 'Ajax Call Failed'
            this.showNotif(msg, 'black')
        },
        missingVal(msg = null) {
            this.showNotif(msg || this.lingoTrans.no_val[this.currentLocale] || 'Missing Value', 'warning')
        }
    },
    watch: {
        activeTab(val) {
            this.updateLs({tab : val})
        },
        selectedDirName(val) {
            this.updateLs({dir : val})
        },
        selectedFileName(val) {
            this.updateLs({file : val})
        }
    },
    render() {}
}
</script>
