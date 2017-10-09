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
            hasNesting: false,
            newItemCounter: 0
        }
    },
    mounted() {
        EventHub.listen('ls-file', (data) => {
            if (data.tab == this.getTabName()) {
                setTimeout(() => {
                    this.selectedFile = data.val
                }, 50)
            }
        })

        EventHub.listen('new_locale_added', () => {
            this.getFileContent()
        })

        EventHub.listen('new_file_added', (data) => {
            this.getFiles()

            if (data.tab == this.getTabName()) {
                setTimeout(() => {
                    this.selectedFile = data.val
                }, 50)
            }
        })
    },
    activated() {
        this.$parent.dirsList = this.dirs
        this.$parent.selectedDirName = this.selectedDir
        this.$parent.localesList = this.locales
        this.$parent.selectedFileName = this.selectedFile
        this.$parent.filesList = this.files
    },
    methods: {
        // data
        getFileContent() {
            $.post(this.routes.selectedFileDataRoute, {
                'file_name': this.selectedFile || null,
                'dir_name': this.selectedDir || null
            }, (data) => {

                if (data.success) {
                    this.locales = data.message.locales
                    this.selectedFileData = data.message.all
                    this.selectedFileDataClone = Object.assign({}, this.selectedFileData)
                    this.hasNesting = data.message.nesting
                } else {
                    this.resetAll(['selectedFile', 'files'])
                }

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

            if (val) {
                this.getFileContent()
            }
        },
        files(val) {
            this.$parent.filesList = val
        }
    }
}
