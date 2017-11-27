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
                        return this.parentMethod('showNotif', (data.message, 'danger'))
                    }

                    this.parentMethod('showNotif', data.message)
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

                }).catch(() => {
                    this.parentMethod('failedAjax')
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
                        return this.parentMethod('showNotif', (data.message, 'danger'))
                    }

                    if (!override) {
                        this.parentMethod('showNotif', data.message)
                    }

                    this.locales.splice(locale, 1)
                    this.parentMethod('getFileContent')

                }).catch(() => {
                    this.parentMethod('failedAjax')
                })
            }
        },
        submitNewData() {
            if (this.dontHaveData()) {
                this.parentMethod('showNotif', (this.trans('empty_file'), 'warning'))
            }

            axios.post(this.routes.saveFileRoute, {
                'file_name': this.selectedFile,
                'dir_name': this.selectedDir || null,
                data: this.formatData()
            }).then(({data}) => {

                if (!data.success) {
                    return this.parentMethod('showNotif', (data.message, 'danger'))
                }

                this.parentMethod('showNotif', data.message)
                this.dataChanged = false
                this.selectedFileData = Object.assign({}, this.selectedFileDataClone)

            }).catch(() => {
                this.parentMethod('failedAjax')
            })
        }
    }
}
