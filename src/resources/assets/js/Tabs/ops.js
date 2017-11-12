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
            keyToCopy : ''
        }
    },
    mounted() {
        EventHub.listen('ls-file', (data) => {
            if (data.tab == this.getTabName()) {
                setTimeout(() => {
                    this.selectedFile = data.val

                    if (data.val == '' && this.getTabName().includes('vendor')) {
                        this.$parent.filesList = []
                    }
                }, 50)
            }
        })

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
        $(document).on('click', '.c2c', () => {
            this.$copyText(this.keyToCopy)
        })
    },
    activated() {
        if (this.$parent.activeTab == this.getTabName()) {
            this.$parent.dirsList = this.dirs
            this.$parent.selectedDirName = this.selectedDir
            this.$parent.localesList = this.locales
            this.$parent.selectedFileName = this.selectedFile
            this.$parent.filesList = this.files
        }
    },
    methods: {
        // data
        getFileContent() {
            $.post(this.routes.selectedFileDataRoute, {
                'file_name': this.selectedFile || null,
                'dir_name': this.selectedDir || null
            }, (data) => {

                if (!data.success) {
                    return this.resetAll(['selectedFile', 'files'])
                }

                this.locales = data.message.locales
                this.selectedFileData = data.message.all
                this.selectedFileDataClone = JSON.parse(JSON.stringify(data.message.all))

            }).fail(() => {
                this.$parent.failedAjax()
            })
        },

        // shared-content
        trans(key) {
            return this.$parent.trans(key) || ''
        },
        reflowTable() {
            this.$parent.reflowTable()
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
            this.$parent.showNotif(msg, s)
        },
        failedAjax() {
            this.showNotif(this.trans('ajax_fail'), 'black')
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
            this.resetAll(['newKeys'])

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
