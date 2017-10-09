export default {
    methods: {
        removeSelectedFile() {
            if (confirm('Are You Sure You Want to Remove This File ?!!')) {

                let removedFile = this.selectedFile
                let currentDir = this.selectedDir
                let currentFiles = Object.values(this.files)
                let lastItem = currentFiles.length == 1 && currentFiles[0] == removedFile

                $.post(this.routes.deleteFileRoute, {
                    'file_name': removedFile,
                    'dir_name': currentDir || null
                }, (data) => {

                    if (data.success) {
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

                        this.selectedDir = currentDir
                        this.parentMethod('getFiles')
                    } else {
                        this.parentMethod('showNotif', (data.message, 'danger'))
                    }

                }).fail(() => {
                    this.parentMethod('failedAjax')
                })
            }
        },
        removeLocale(locale, override = null) {
            if (override || confirm('Are You Sure You Want to Remove This Localized Version ?!!')) {
                $.post(this.routes.deleteLocaleRoute, {
                    'locale': locale,
                    'dir_name': this.selectedDir || null
                }, (data) => {

                    if (data.success) {
                        if (!override) {
                            this.parentMethod('showNotif', data.message)
                        }

                        this.locales.splice(locale, 1)
                        this.parentMethod('getFileContent')
                    } else {
                        this.parentMethod('showNotif', (data.message, 'danger'))
                    }

                }).fail(() => {
                    this.parentMethod('failedAjax')
                })
            }
        },
        submitNewData() {
            if (this.dontHaveData()) {
                this.parentMethod('showNotif', ('Maybe You Want To Delete the File Instead ?!!', 'warning', 'danger'))
            }

            $.post(this.routes.saveFileRoute, {
                'file_name': this.selectedFile,
                'dir_name': this.selectedDir || null,
                data: this.formatData()
            }, (data) => {

                if (data.success) {
                    this.parentMethod('showNotif', data.message)
                    this.dataChanged = false
                    this.selectedFileData = Object.assign({}, this.selectedFileDataClone)
                } else {
                    this.parentMethod('showNotif', (data.message, 'danger'))
                }

            }).fail(() => {
                this.parentMethod('failedAjax')
            })
        }
    }
}
