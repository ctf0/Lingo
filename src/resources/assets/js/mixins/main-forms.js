export default {
    methods: {
        scanForMissing() {
            $.get(this.scanForMissingRoute, (data) => {
                this.showNotif(data.message)
            }).fail(() => {
                this.failedAjax()
            })
        },
        addNewLocale() {
            if (!this.new_locale) {
                return this.missingVal()
            }

            $.post(this.addNewTransRoute, {
                'file_name': this.new_locale,
                'dir_name' : this.selectedDirName || null
            }, (data) => {

                if (data.success) {
                    this.showNotif(data.message)
                    this.resetAll(['new_locale'])
                    EventHub.fire('new_locale_added')
                } else {
                    this.showNotif(data.message, 'danger')
                }

            }).fail(() => {
                this.failedAjax()
            })
        },
        addNewFile() {
            if (!this.new_file) {
                return this.missingVal()
            }

            let file_name = this.new_file

            $.post(this.addNewFileRoute, {
                'file_name': file_name,
                'dir_name' : this.selectedDirName || null
            }, (data) => {

                if (data.success) {
                    this.showNotif(data.message)
                    this.resetAll(['new_file', 'filesList'])

                    EventHub.fire('new_file_added', {
                        tab: this.activeTab,
                        val: file_name
                    })
                } else {
                    this.showNotif(data.message, 'danger')
                }

            }).fail(() => {
                this.failedAjax()
            })
        },
        addNewVendor() {
            if (!this.new_vendor) {
                return this.missingVal()
            }

            let vendor_name = this.new_vendor

            $.post(this.addNewVendorRoute, {
                'dir_name' : vendor_name
            }, (data) => {

                if (data.success) {
                    this.showNotif(data.message)
                    this.resetAll(['new_vendor'])
                    EventHub.fire('new_vendor_added', vendor_name)
                } else {
                    this.showNotif(data.message, 'danger')
                }

            }).fail(() => {
                this.failedAjax()
            })
        }
    }
}
