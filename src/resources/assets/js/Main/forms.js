export default {
    methods: {
        scanForMissing() {
            $.get(this.scanForMissingRoute, (data) => {
                this.showNotif(data.message)
                EventHub.fire('scan_complete', {tab: this.activeTab})
            }).fail(() => {
                this.failedAjax()
            })
        },
        addNewLocale() {
            if (!this.new_locale) {
                return this.missingVal()
            }

            if (!this.$refs.locale.disabled) {
                $.post(this.addNewTransRoute, {
                    'file_name': this.new_locale,
                    'dir_name' : this.selectedDirName || null
                }, (data) => {

                    if (!data.success) {
                        return this.showNotif(data.message, 'danger')
                    }

                    this.showNotif(data.message)
                    this.resetAll(['new_locale'])
                    EventHub.fire('new_locale_added', {tab: this.activeTab})

                }).fail(() => {
                    this.failedAjax()
                })
            }
        },
        addNewFile() {
            if (!this.new_file) {
                return this.missingVal()
            }

            if (!this.$refs.file.disabled) {
                let file_name = this.new_file

                $.post(this.addNewFileRoute, {
                    'file_name': file_name,
                    'dir_name' : this.selectedDirName || null
                }, (data) => {

                    if (!data.success) {
                        return this.showNotif(data.message, 'danger')
                    }

                    this.showNotif(data.message)
                    this.resetAll(['new_file', 'filesList'])

                    EventHub.fire('new_file_added', {
                        tab: this.activeTab,
                        val: file_name
                    })

                }).fail(() => {
                    this.failedAjax()
                })
            }
        },
        addNewVendor() {
            if (!this.new_vendor) {
                return this.missingVal()
            }

            if (!this.$refs.vendor.disabled) {
                let vendor_name = this.new_vendor

                $.post(this.addNewVendorRoute, {
                    'dir_name' : vendor_name
                }, (data) => {

                    if (!data.success) {
                        return this.showNotif(data.message, 'danger')
                    }

                    this.showNotif(data.message)
                    this.resetAll(['new_vendor'])
                    EventHub.fire('new_vendor_added', vendor_name)

                }).fail(() => {
                    this.failedAjax()
                })
            }
        }
    }
}