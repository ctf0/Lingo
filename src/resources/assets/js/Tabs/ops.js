export default {
    data() {
        return {
            routes: {
                filesRoute : this.$parent.filesRoute,
                selectedFileDataRoute : this.$parent.selectedFileDataRoute,
                deleteFileRoute : this.$parent.deleteFileRoute,
                deleteLocaleRoute : this.$parent.deleteLocaleRoute,
                saveFileRoute : this.$parent.saveFileRoute
            },
            files: [],
            selectedFile: '',
            locales: [],
            selectedFileData: '',
            selectedFileDataClone: '',
            newKeys: '',
            dataChanged: false,
            newItemCounter: 0,
            keyToCopy : '',
            currentInputRef: ''
        }
    },
    mounted() {
        this.$tippy.forceUpdateHtml()

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
                this.getFiles()

                setTimeout(() => {
                    this.selectedFile = data.val
                }, 50)
            }
        })

        // before refresh
        window.onbeforeunload = () => {
            if (this.dataChanged === true) {
                return confirm('Confirm refresh')
            }
        }

        // copy to clipboard
        this.$nextTick(() => {
            document.body.onclick = (e) => {
                e = window.event ? e.srcElement : e.target
                if (e.className && e.className.includes('c2c')) {
                    this.$copyText(this.keyToCopy)
                    this.refocus()
                }
            }
        })
    },
    activated() {
        this.preVisited()

        if (this.$parent.activeTab == this.getTabName()) {
            this.$parent.dirsList = this.dirs
            this.$parent.localesList = this.locales
            this.$parent.filesList = this.files
            this.$parent.selectedFileName = this.selectedFile
        }
    },
    methods: {
        preVisited() {
            let ls = this.parentMethod('getLs')

            if (ls) {
                EventHub.fire('ls-dir', ls.dir)

                if (ls.tab == this.getTabName()) {
                    setTimeout(() => {
                        if (this.files.includes(ls.file) && !this.selectedFile) {
                            this.selectedFile = ls.file
                        }
                    }, 100)
                }

                if (this.getTabName().includes('vendor') && ls.file == '') {
                    this.$parent.filesList = []
                }
            }
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
                    this.selectedFileDataClone = JSON.parse(JSON.stringify(all))
                }

            }).catch((err) => {
                console.error(err)
                this.parentMethod('failedAjax')
            })
        },

        // shared-content
        trans(key) {
            return this.parentMethod('trans', key) || ''
        },
        getTabName() {
            return this.$options.name
        },
        resetAll(items) {
            items.forEach((e) => {
                this[e] = ''
            })
        },
        showNotif(msg, s = 'success') {
            this.parentMethod('showNotif', (msg, s))
        },
        failedAjax() {
            this.showNotif(this.trans('ajax_error'), 'black')
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
        },
        refocus() {
            // if (this.currentInputRef) {
            //     return this.currentInputRef.target.focus()
            // }
        }
    },
    watch: {
        selectedFileDataClone(val) {
            this.$nextTick(() => {
                if (Object.keys(val).length == 0) {
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
            this.resetAll(['newKeys', 'currentInputRef', 'keyToCopy'])

            if (val) {
                if (this.getTabName().includes('vendor') && !this.selectedDirName) {
                    return
                }

                this.getFileContent()
            }
        },
        files(val) {
            this.$parent.filesList = val
        }
    }
}
