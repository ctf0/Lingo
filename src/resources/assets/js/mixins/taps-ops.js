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
        EventHub.listen('new_locale_added', () => {
            this.getFileContent()
        })

        EventHub.listen('ls-file', (data) => {
            if (data.tab == this.getTabName()) {
                setTimeout(() => {
                    this.selectedFile = data.val
                }, 50)
            }
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
    updated() {
        this.tableFloatHead($('table'), $('#menu').outerHeight(true))
        this.tableColumnResize()
    },
    activated() {
        EventHub.fire('locales_fetched', {
            tab: this.getTabName(),
            val: this.locales
        })
        EventHub.fire('files_fetched', {
            tab: this.getTabName(),
            val: this.files
        })
        EventHub.fire('dirs_fetched', this.dirs)
        EventHub.fire('dir_got_selected', this.selectedDir)
        EventHub.fire('file_got_selected', {
            tab: this.getTabName(),
            val: this.selectedFile
        })
    },
    methods: {
        // table ops
        tableFloatHead(table, top) {
            setTimeout(() => {
                table.floatThead({
                    top: top,
                    autoReflow: true,
                    responsiveContainer(table) {
                        return table.closest('section')
                    }
                })
            }, 600)

            this.$parent.reflowTable()
        },
        tableColumnResize() {
            let thElm
            let startOffset

            document.querySelectorAll('table th').forEach((th) => {
                th.style.position = 'relative'

                let grip = document.createElement('div')
                grip.innerHTML = '&nbsp;'
                grip.style.top = 0
                grip.style.right = 0
                grip.style.bottom = 0
                grip.style.width = '10px'
                grip.style.position = 'absolute'
                grip.style.cursor = 'col-resize'
                grip.addEventListener('mousedown', (e) => {
                    thElm = th
                    startOffset = th.offsetWidth - e.pageX
                })

                th.appendChild(grip)
            })

            document.addEventListener('mousemove', (e) => {
                if (thElm) {
                    this.$parent.reflowTable()
                    thElm.style.width = startOffset + e.pageX + 'px'
                }
            })

            document.addEventListener('mouseup', () => {
                thElm = undefined
            })
        },

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
        dontHaveData() {
            return Object.keys(this.selectedFileDataClone).length == 0
        },

        // crud
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
                        this.$parent.showNotif(data.message)
                        this.resetAll(['selectedFile', 'selectedDir'])
                        this.resetData()

                        if (lastItem) {
                            this.selectedDir = currentDir

                            // remove locales as they are useless
                            return this.locales.forEach((e) => {
                                this.removeLocale(e, 'y')
                            })
                        }

                        this.selectedDir = currentDir
                        this.getFiles()
                    } else {
                        this.$parent.showNotif(data.message, 'danger')
                    }

                }).fail(() => {
                    this.$parent.failedAjax()
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
                            this.$parent.showNotif(data.message)
                        }

                        this.locales.splice(locale, 1)
                        this.getFileContent()
                    } else {
                        this.$parent.showNotif(data.message, 'danger')
                    }

                }).fail(() => {
                    this.$parent.failedAjax()
                })
            }
        },
        submitNewData() {
            if (this.dontHaveData()) {
                return this.$parent.missingVal('Maybe You Want To Delete the File Instead ?!!')
            }

            $.post(this.routes.saveFileRoute, {
                'file_name': this.selectedFile,
                'dir_name': this.selectedDir || null,
                data: this.formatData()
            }, (data) => {

                if (data.success) {
                    this.$parent.showNotif(data.message)
                    this.dataChanged = false
                    this.selectedFileData = Object.assign({}, this.selectedFileDataClone)
                } else {
                    this.$parent.showNotif(data.message, 'danger')
                }

            }).fail(() => {
                this.$parent.failedAjax()
            })
        },

        // ops
        addNewItem() {
            let name = 'newItem' + this.newItemCounter
            let fileData = this.selectedFileDataClone

            this.dataChanged = true

            // incase we already have keys == name
            if (fileData.hasOwnProperty(name)) {
                this.newItemCounter++
                return this.addNewItem()
            }

            this.locales.forEach((item) => {
                if (!fileData.hasOwnProperty(name)) {
                    this.$set(fileData, name, {[item] :'some value'})
                } else {
                    this.$set(fileData[name], item, 'some value')
                }
            })

            this.newItemCounter++
        },
        removeItem(item) {
            this.dataChanged = true
            this.$delete(this.selectedFileDataClone, item)
        },
        resetData() {
            this.dataChanged = false
            this.newItemCounter = 0

            // because for some reason the original "selectedFileData" gets updated as well
            // which makes it impossible to reset from with changed "keys & values"
            if (Object.keys(this.selectedFileDataClone).length == Object.keys(this.selectedFileData).length) {
                let old = this.selectedFile

                setTimeout(() => {
                    this.selectedFile = old
                }, 50)

                return this.resetAll(['newKeys', 'selectedFile'])
            }

            this.selectedFileDataClone = Object.assign({}, this.selectedFileData)
        },

        // util
        resetAll(items) {
            items.forEach((e) => {
                this[e] = ''
            })
        },
        saveNewKey(e) {
            this.$parent.reflowTable()

            let old_key = e.target.dataset.mainKey
            let new_key = e.target.innerText

            this.dataChanged = true

            if (this.newKeys) {
                return this.newKeys[old_key] = new_key
            }

            this.newKeys = {[old_key] : new_key}
        },
        saveNewValue(e) {
            this.$parent.reflowTable()

            let code = e.target.dataset.code
            let key = e.target.dataset.mainKey
            let value = e.target.innerText

            this.dataChanged = true
            this.$set(this.selectedFileDataClone[key], code, value)
        },
        formatData() {
            let main = this.selectedFileDataClone
            let newData = this.newKeys

            let main_keys = Object.keys(main)
            let newData_keys = Object.keys(newData)

            // replace changed keys
            if (newData_keys.length) {
                main_keys.map((old_key) => {
                    if (newData_keys.includes(old_key)) {
                        let new_key = newData[old_key]

                        Object.defineProperty(
                            main,
                            new_key,
                            Object.getOwnPropertyDescriptor(main, old_key)
                        )

                        delete main[old_key]
                    }
                })
            }

            return main
        },
        getTabName() {
            return this.$options.name
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
            EventHub.fire('locales_fetched', {
                tab: this.getTabName(),
                val: val
            })
        },
        selectedFile(val) {
            EventHub.fire('file_got_selected', {
                tab: this.getTabName(),
                val: val
            })

            if (val) {
                this.getFileContent()
            }
        },
        files(val) {
            EventHub.fire('files_fetched', {
                tab: this.getTabName(),
                val: val
            })
        }
    }
}
