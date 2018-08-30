import cloneDeep from 'lodash/cloneDeep'

export default {
    methods: {
        removeSelectedFile() {
            if (confirm(this.trans('you_sure_file'))) {

                let removedFile = this.selectedFile
                let currentDir = this.selectedDir
                let currentFiles = Object.values(this.files)
                let lastItem = currentFiles.length == 1 && currentFiles[0] == removedFile

                axios.post(this.routes.deleteFileRoute, {
                    'file_name': removedFile,
                    'dir_name': currentDir || null
                }).then(({data}) => {

                    if (!data.success) {
                        return this.showNotif(data.message, 'danger')
                    }

                    this.showNotif(data.message)
                    this.parentMethod('resetAll', ['selectedFile', 'selectedDir'])
                    this.resetData()

                    if (lastItem) {
                        this.selectedDir = currentDir

                        // remove locales as they are useless
                        return this.locales.forEach((e) => {
                            this.removeLocale(e, 'y')
                        })
                    }

                    this.locales = []
                    this.selectedDir = currentDir
                    this.parentMethod('getFiles')

                }).catch((err) => {
                    console.error(err)
                    this.failedAjax()
                })
            }
        },
        removeLocale(locale, override = null) {
            if (override || confirm(this.trans('you_sure_locale'))) {
                axios.post(this.routes.deleteLocaleRoute, {
                    'locale': locale,
                    'dir_name': this.selectedDir || null
                }).then(({data}) => {

                    if (!data.success) {
                        return this.showNotif(data.message, 'danger')
                    }

                    if (!override) {
                        this.showNotif(data.message)
                    }

                    this.locales.splice(locale, 1)
                    this.parentMethod('getFileContent')

                }).catch((err) => {
                    console.error(err)
                    this.failedAjax()
                })
            }
        },

        // save
        formatData() {
            let main = this.selectedFileDataClone
            let newData = this.newKeys
            let newData_keys = Object.keys(newData)

            // replace changed keys
            if (newData_keys.length) {
                main.map((item, i) => {
                    if (newData_keys.includes(item.name)) {
                        return main[i].name = newData[item.name]
                    }
                })
            }

            return main
        },
        submitNewData() {
            let formatData = this.formatData()

            if (this.noData()) {
                this.showNotif(this.trans('empty_file'), 'warning')
            }

            axios.post(this.routes.saveFileRoute, {
                'file_name': this.selectedFile,
                'dir_name': this.selectedDir || null,
                data: formatData
            }).then(({data}) => {

                if (!data.success) {
                    return this.showNotif(data.message, 'danger')
                }

                Array.from(document.querySelectorAll('.changedKeys')).forEach((e) => {
                    e.classList.remove('changedKeys')
                })

                this.dataChanged = false
                this.newKeys = ''
                this.newItemCounter = 0
                this.selectedFileData = cloneDeep(formatData)
                this.showNotif(data.message)
                // this.resetSearch()

            }).catch((err) => {
                console.error(err)
                this.failedAjax()
            })
        }
    }
}
