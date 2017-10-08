<template>
    <div>
        <!-- select package -->
        <div class="field is-grouped is-grouped-right" v-if="dirs.length">
            <div class="control has-icons-left">
                <div class="select">
                    <select v-model="selectedDir">
                        <option value="" disabled>Select a Package</option>
                        <option v-for="(d, i) in dirs" :key="i">{{ d }}</option>
                    </select>
                </div>
                <div class="icon is-small is-left"><i class="fa fa-folder"></i></div>
            </div>
            <!-- remove -->
            <div class="control" v-if="selectedDir">
                <button class="button is-danger" @click="removeSelectedDir()">
                    <span class="icon">
                        <i class="fa fa-trash"></i>
                    </span>
                </button>
            </div>
        </div>

        <template v-if="selectedDir">
            <!-- select file -->
            <div class="field is-grouped is-grouped-right" v-if="files.length">
                <div class="control has-icons-left">
                    <div class="select">
                        <select v-model="selectedFile">
                            <option value="" disabled>Select a File</option>
                            <option v-for="(f, i) in files" :key="i">{{ f }}</option>
                        </select>
                    </div>
                    <div class="icon is-small is-left"><i class="fa fa-file"></i></div>
                </div>
                <div class="control" v-if="selectedFile">
                    <button class="button is-danger" @click="removeSelectedFile()">
                        <span class="icon">
                            <i class="fa fa-trash"></i>
                        </span>
                    </button>
                </div>
            </div>

            <section v-if="selectedFile" class="m-t-50">
                <!-- warning -->
                <article class="message is-warning" v-if="hasNesting">
                    <div class="message-header">
                        <p>Warning</p>
                        <button class="delete" aria-label="delete" @click="hasNesting = false"></button>
                    </div>
                    <div class="message-body">Nested Arrays Detected</div>
                </article>

                <!-- data -->
                <table class="table is-fullwidth is-bordered">
                    <thead>
                        <tr class="is-unselectable">
                            <th width="1%">key</th>
                            <th v-for="(l, i) in locales" :key="i">
                                <div class="tags has-addons">
                                    <span class="tag is-dark is-medium">{{ l }}</span>
                                    <span class="tag is-warning is-medium" @click="removeLocale(l)">
                                        <i class="fa fa-trash"></i>
                                    </span>
                                </div>
                            </th>
                            <th>Operations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(mainV, mainK, mainI) in selectedFileDataClone" :key="mainI">
                            <td nowrap contenteditable
                                dir="auto"
                                v-text="mainK"
                                :data-main-key="mainK"
                                @keydown.enter.prevent
                                @input="saveNewKey($event)">
                            </td>
                            <td v-for="(nestV, nestK, nestI) in mainV" :key="nestI"
                                contenteditable
                                dir="auto"
                                v-text="nestV"
                                :data-code="nestK"
                                :data-main-key="mainK"
                                @keydown.enter.prevent
                                @input="saveNewValue($event)">
                            </td>
                            <td width="1%">
                                <button class="button is-danger" @click="removeItem(mainK)">
                                    <span class="icon">
                                        <i class="fa fa-trash"></i>
                                    </span>
                                </button>
                            </td>
                        </tr>

                        <!-- nothing found -->
                        <tr v-if="dontHaveData()">
                            <td :colspan="locales.length + 2">
                                No Data To Display
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- ops -->
                <div class="level">
                    <div class="level-right">
                        <div class="level-item">
                            <button class="button is-info" @click.prevent="addNewItem()">Add New Key</button>
                        </div>
                    </div>
                    <div class="level-left">
                        <div class="level-item">
                            <button class="button is-success" :disabled="!dataChanged" @click="submitNewData()">Save Changes</button>
                        </div>
                        <div class="level-item">
                            <button class="button" :disabled="!dataChanged" @click="resetData()">Reset Changes</button>
                        </div>
                    </div>
                </div>
            </section>
        </template>
    </div>
</template>

<style scoped>
    .tag {
        border-radius: 3px;
    }
</style>

<script>
import Ops from './../mixins/taps-ops'

export default {
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
    mounted() {
        this.getDirs()

        EventHub.listen('new_vendor_added', (val) => {
            this.getDirs()
            this.selectedDir = val
        })

        EventHub.listen('ls-dir', (val) => {
            this.selectedDir = val
        })
    },
    methods: {
        getDirs() {
            $.get(this.dirsRoute, (data) => {
                this.dirs = data
            }).fail(() => {
                this.$parent.failedAjax()
            })
        },
        getFiles() {
            $.post(this.routes.filesRoute, {
                'dir_name' : this.selectedDir
            }, (data) => {

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

            }).fail(() => {
                this.$parent.failedAjax()
            })
        },
        removeSelectedDir() {
            if (confirm('Are You Sure You Want to Remove This Package Translations ?!!')) {
                $.post(this.deleteVendorRoute, {
                    'dir_name' : this.selectedDir
                }, (data) => {

                    if (data.success) {
                        this.$parent.showNotif(data.message)

                        this.resetAll([
                            'selectedDir',
                            'selectedFile',
                            'locales',
                            'files',
                            'selectedFileData',
                            'selectedFileDataClone'
                        ])

                        this.getDirs()
                    } else {
                        this.$parent.showNotif(data.message, 'danger')
                    }

                }).fail(() => {
                    this.$parent.failedAjax()
                })
            }
        }
    },
    watch: {
        dirs(val) {
            EventHub.fire('dirs_fetched', val)
        },
        selectedDir(val) {
            EventHub.fire('dir_got_selected', val)

            if (val) {
                this.newItemCounter = 0
                this.getFiles()
            }
        }
    }
}
</script>
