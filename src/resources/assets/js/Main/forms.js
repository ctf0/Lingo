export default {
    methods: {
        scanForMissing() {
            axios.get(this.routes.scanForMissingRoute)
                .then(({data}) => {
                    this.showNotif(data.message, 'link', 5)
                    EventHub.fire('scan_complete', {tab: this.activeTab})
                }).catch((err) => {
                    console.error(err)
                    this.failedAjax()
                })
        },
        addNewLocale() {
            if (!this.new_locale) {
                return this.missingVal()
            }

            if (!this.$refs.locale.disabled) {
                axios.post(this.routes.addNewLocaleRoute, {
                    'file_name': this.new_locale,
                    'dir_name': this.vendorCheck() ? this.selectedDirName : null
                }).then(({data}) => {

                    if (!data.success) {
                        return this.showNotif(data.message, 'danger')
                    }

                    this.showNotif(data.message)
                    this.resetAll(['new_locale'])
                    EventHub.fire('new_locale_added', {tab: this.activeTab})

                }).catch((err) => {
                    console.error(err)
                    this.failedAjax()
                })
            }
        },
        addNewFile() {
            if (!this.new_file) {
                return this.missingVal()
            }

            if (!this.new_file.includes('.php')) {
                return this.missingVal('.php ?!!')
            }

            if (!this.$refs.file.disabled) {
                let file_name = this.new_file

                axios.post(this.routes.addNewFileRoute, {
                    'file_name': file_name,
                    'dir_name': this.selectedDirName || null
                }).then(({data}) => {

                    if (!data.success) {
                        return this.showNotif(data.message, 'danger')
                    }

                    this.showNotif(data.message)
                    this.resetAll(['new_file', 'filesList'])

                    EventHub.fire('new_file_added', {
                        tab: this.activeTab,
                        val: file_name
                    })

                }).catch((err) => {
                    console.error(err)
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

                axios.post(this.routes.addNewVendorRoute, {
                    'dir_name': vendor_name
                }).then(({data}) => {

                    if (!data.success) {
                        return this.showNotif(data.message, 'danger')
                    }

                    this.showNotif(data.message)
                    this.resetAll(['new_vendor'])
                    EventHub.fire('new_vendor_added', vendor_name)

                }).catch((err) => {
                    console.error(err)
                    this.failedAjax()
                })
            }
        }
    }
}
