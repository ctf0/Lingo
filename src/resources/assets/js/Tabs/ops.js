import debounce from 'lodash/debounce'
import cloneDeep from 'lodash/cloneDeep'

export default {
    data() {
        return {
            routes: this.$parent.routes,
            searchFor: '',
            files: [],
            selectedFile: '',
            locales: [],
            selectedFileData: '',
            selectedFileDataClone: '',
            dataChanged: false,
            THeadTop: {'--tHead': 0},

            newKeys: '',
            newItemCounter: 0,
            keyToCopy: '',
            copiedItem: '',
            toBeMergedKeys: [],
            mergerName: '',
            showModal: false,
            orderDirection: 'asc'
        }
    },
    props: [
        'dirsRoute',
        'deleteVendorRoute'
    ],
    mounted() {
        EventHub.listen('scan_complete', (data) => {
            if (data.tab == this.getTabName() && this.selectedFile !== '') {
                this.getFileContent()
            }
        })

        EventHub.listen('new_locale_added', (data) => {
            if (data.tab == this.getTabName()) {
                this.getFileContent()
            }
        })

        EventHub.listen('new_file_added', (data) => {
            if (data.tab == this.getTabName()) {

                this.getFiles().then(() => {
                    if (this.files.length > 0) {
                        if (this.files.includes(data.val)) {
                            this.selectedFile = data.val
                        }
                    }
                })
            }
        })

        // before refresh
        window.onbeforeunload = () => {
            if (this.dataChanged === true) {
                return confirm('Confirm refresh')
            }
        }
    },
    activated() {
        this.preVisited().then(() => {
            // get copied item across tabs
            if (this.$parent.copiedItem) {
                this.copiedItem = this.$parent.copiedItem
            }

            if (this.$parent.activeTab == this.getTabName()) {
                this.$parent.dirsList = this.dirs
                this.$parent.localesList = this.locales
                this.$parent.filesList = this.files
                this.$parent.selectedFileName = this.selectedFile
            }
        })
    },
    methods: {
        preVisited() {
            return new Promise((resolve, rej) => {
                let ls = this.parentMethod('getLs')

                if (Object.keys(ls).length) {
                    // vendor
                    if (this.getTabName().includes('vendor')) {
                        EventHub.fire('ls-dir', ls.dir)

                        if (!ls.file) {
                            this.$parent.filesList = []
                        }

                        return resolve()
                    }

                    // normal
                    let t = setInterval(() => {
                        if (this.files.length > 0) {
                            if (this.files.includes(ls.file)) {
                                this.selectedFile = ls.file
                            }
                            clearInterval(t)
                            return resolve()
                        }
                    }, 100)
                }
            })
        },
        updateTHead() {
            this.$nextTick(debounce(() => {
                let sec = document.querySelector('.sticky-section')
                let count = sec.clientHeight + parseInt(window.getComputedStyle(sec).getPropertyValue('top')) - 1
                this.THeadTop = {
                    '--tHead': `${count}px`
                }
            }, 500))
        },

        // data
        getFileContent() {
            axios.post(this.routes.selectedFileDataRoute, {
                'file_name': this.selectedFile || null,
                'dir_name': this.selectedDir || null
            }).then(({data}) => {
                if (!data.success) {
                    return this.resetAll(['selectedFile', 'files'])
                }

                this.locales = data.message.locales
                let all = data.message.all
                if (all) {
                    this.selectedFileData = all
                    this.selectedFileDataClone = cloneDeep(all)
                }

            }).catch((err) => {
                console.error(err)
                this.failedAjax()
            })
        },

        // shared-content
        getTabName() {
            return this.$options.name
        },
        resetAll(items) {
            items.forEach((e) => {
                this[e] = ''
            })
        },

        // parent
        trans(key) {
            return this.parentMethod('trans', key) || ''
        },
        failedAjax() {
            return this.parentMethod('failedAjax')
        },
        showNotif(msg, s = 'success') {
            this.$parent.showNotif(msg, s)
        },
        parentMethod(method_name, args = null) {
            return this.$parent[method_name](args)
        },

        // copy key
        getFileName() {
            return this.selectedFile.replace(/(.[^.]*)$/, '')
        },
        getKey(key) {
            let str = this.getTabName().includes('vendor')
                ? `${this.selectedDir}::${this.getFileName()}.${key}`
                : `${this.getFileName()}.${key}`

            let rep = this.$parent.selectedKeyFormat !== ''
                ? this.$parent.selectedKeyFormat.replace('value', str)
                : `'${str}'`

            return rep
        }
    },
    watch: {
        selectedFileDataClone(val) {
            this.$nextTick(() => {
                if (val.length == 0) {
                    this.resetAll(['newKeys'])
                }
            })
        },
        locales(val) {
            this.$parent.localesList = val
        },
        selectedFile(val) {
            this.$parent.selectedFileName = val
            this.dataChanged = false
            this.resetAll(['newKeys', 'keyToCopy'])

            if (val) {
                this.updateTHead()

                return this.getTabName().includes('vendor') && !this.selectedDir
                    ? false
                    : this.getFileContent()
            }
        },
        files(val) {
            this.$parent.filesList = val
        },
        copiedItem(val) {
            this.$parent.copiedItem = val
        }
    }
}
