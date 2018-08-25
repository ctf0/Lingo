<template>
    <shared-content>
        <!-- select package -->
        <div v-if="dirs.length" class="field is-grouped is-grouped-right">
            <div class="control has-icons-left">
                <div class="select">
                    <select v-model="selectedDir">
                        <option value="" disabled>{{ trans('select_dir') }}</option>
                        <option v-for="(d, i) in dirs" :key="i">{{ d }}</option>
                    </select>
                </div>
                <div class="icon is-small is-left"><icon name="folder"/></div>
            </div>
            <div v-if="selectedDir" class="control">
                <!-- remove -->
                <button class="button is-danger" @click="removeSelectedDir()">
                    <span class="icon"><icon name="trash"/></span>
                </button>
                <!-- download -->
                <form :action="routes.downloadVendorRoute" method="get" class="is-inline-block">
                    <input :value="selectedDir" type="hidden" name="dir_name">
                    <button type="submit" class="button is-outlined">
                        <span class="icon"><icon name="download"/></span>
                    </button>
                </form>
            </div>
        </div>
    </shared-content>
</template>

<script>
import Ops from './ops'
import SharedContent from './shared/content'

export default {
    components: {SharedContent},
    name: 'vendor-tab',
    mixins: [Ops],
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
        EventHub.listen('new_vendor_added', (val) => {
            this.getDirs()
            this.selectedDir = val
        })

        EventHub.listen('ls-dir', (val) => {
            let t = setInterval(() => {
                if (this.dirs.length > 0) {
                    if (this.dirs.includes(val)) {
                        this.selectedDir = val
                    }
                    clearInterval(t)
                }
            }, 100)
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
            this.resetAll(['selectedFile'])

            return axios.post(this.routes.filesRoute, {
                'dir_name': this.selectedDir
            }).then(({data}) => {

                if (data.success) {
                    this.files = data.message

                    if (this.files.length > 0) {
                        this.files.length > 1
                            ? false
                            : this.selectedFile = this.files[0]
                    } else {
                        // when changing dirs with files
                        this.resetAll(['locales'])
                    }
                } else {
                    // new vendor / no files
                    this.resetAll(['locales'])
                }

            }).catch((err) => {
                console.error(err)
                this.failedAjax()
            })
        },
        removeSelectedDir() {
            if (confirm(this.trans('you_sure_package'))) {
                axios.post(this.deleteVendorRoute, {
                    'dir_name': this.selectedDir
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
            this.keysToBeMerged = []
            this.resetAll(['newKeys'])

            if (val) {
                this.newItemCounter = 0
                this.getFiles()
            }
        }
    }
}
</script>
