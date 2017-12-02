<template>
    <div>
        <!-- select package -->
        <div class="field is-grouped is-grouped-right" v-if="dirs.length">
            <div class="control has-icons-left">
                <div class="select">
                    <select v-model="selectedDir">
                        <option value="" disabled>{{ trans('select_dir') }}</option>
                        <option v-for="(d, i) in dirs" :key="i">{{ d }}</option>
                    </select>
                </div>
                <div class="icon is-small is-left"><i class="fa fa-folder"/></div>
            </div>
            <!-- remove -->
            <div class="control" v-if="selectedDir">
                <button class="button is-danger" @click="removeSelectedDir()">
                    <span class="icon">
                        <i class="fa fa-trash"/>
                    </span>
                </button>
            </div>
        </div>

        <template v-if="selectedDir">
            <!-- select file -->
            <shared-content/>
        </template>
    </div>
</template>

<script>
import Ops from './ops'
import SharedContent from './shared/content'

export default {
    components: {SharedContent},
    name: 'vendor-tab',
    mixins: [Ops],
    props: [
        'dirsRoute',
        'deleteVendorRoute'
    ],
    data() {
        return {
            dirs: [],
            selectedDir: ''
        }
    },
    beforeMount() {
        this.getDirs()
    },
    mounted() {
        EventHub.listen('ls-dir', (val) => {
            setTimeout(() => {
                if (this.dirs.length && this.dirs.includes(val)) {
                    return this.selectedDir = val
                }
            }, 100)
        })

        EventHub.listen('new_vendor_added', (val) => {
            this.getDirs()
            this.selectedDir = val
        })
    },
    methods: {
        getDirs() {
            axios.get(this.dirsRoute).then(({data}) => {
                this.dirs = data
            }).catch((err) => {
                console.error(err)
                this.failedAjax()
            })
        },
        getFiles() {
            axios.post(this.routes.filesRoute, {
                'dir_name' : this.selectedDir
            }).then(({data}) => {

                if (data.success) {
                    this.files = data.message

                    if (this.files.length == 1) {
                        this.selectedFile = this.files[0]
                    } else {
                        // when changing dirs with files
                        this.resetAll(['selectedFile', 'locales'])
                    }
                } else {
                    // new vendor / no files
                    this.resetAll(['selectedFile', 'locales'])
                }

                // get file data or avail locales
                this.getFileContent()

            }).catch((err) => {
                console.error(err)
                this.failedAjax()
            })
        },
        removeSelectedDir() {
            if (confirm(this.trans('you_sure_package'))) {
                axios.post(this.deleteVendorRoute, {
                    'dir_name' : this.selectedDir
                }).then(({data}) => {

                    if (!data.success) {
                        return this.showNotif(data.message, 'danger')
                    }

                    this.showNotif(data.message)

                    this.resetAll([
                        'selectedDir',
                        'selectedFile',
                        'locales',
                        'files',
                        'selectedFileData',
                        'selectedFileDataClone'
                    ])

                    this.getDirs()

                }).catch((err) => {
                    console.error(err)
                    this.failedAjax()
                })
            }
        }
    },
    watch: {
        dirs(val) {
            this.$parent.dirsList = val
        },
        selectedDir(val) {
            this.$parent.selectedDirName = val
            this.resetAll(['newKeys', 'currentInputRef', 'keyToCopy'])

            if (val) {
                this.newItemCounter = 0
                this.getFiles()
            }
        }
    }
}
</script>
