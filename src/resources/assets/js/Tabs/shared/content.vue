<template>
    <div>
        <!-- select file -->
        <div v-if="files.length" class="field is-grouped is-grouped-right">
            <div class="control has-icons-left">
                <div class="select">
                    <select v-model="selectedFile">
                        <option value="" disabled>{{ trans('select_file') }}</option>
                        <option v-for="(f, i) in files" :key="i">{{ f }}</option>
                    </select>
                </div>
                <div class="icon is-small is-left"><icon name="file"/></div>
            </div>
            <div v-if="selectedFile" class="control">
                <button class="button is-danger" @click="removeSelectedFile()">
                    <span class="icon">
                        <icon name="trash"/>
                    </span>
                </button>
            </div>
        </div>

        <div v-if="selectedFile" class="level is-mobile is-marginless m-t-40">
            <!-- items count -->
            <div class="level-left">
                <div class="level-item">
                    <div class="control">
                        <h4 class="title is-4">"{{ itemsCount }}" Item/s</h4>
                    </div>
                </div>
            </div>

            <div class="level-right">
                <div class="level-item is-marginless">
                    <!-- search -->
                    <div class="field has-addons">
                        <p class="control has-icons-left">
                            <input v-model="searchFor"
                                   :placeholder="trans('find')"
                                   class="input"
                                   type="text">
                            <span class="icon is-left">
                                <icon name="search"/>
                            </span>
                        </p>
                        <p class="control">
                            <button :disabled="!searchFor" class="button is-black"
                                    @click="resetSearch()">
                                <span class="icon"><icon name="times"/></span>
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- data -->
        <section v-if="selectedFile" class="m-t-30">
            <!-- table -->
            <table class="table is-fullwidth is-hoverable is-bordered">
                <thead>
                    <tr class="is-unselectable">
                        <th class="is-link" width="1%">
                            <div class="field has-addons is-marginless">
                                <div class="control">
                                    <button :disabled="toBeMergedKeys.length < 2" class="button is-borderless" @click="toggleModal(true)">
                                        {{ trans('merge_keys') }}
                                    </button>
                                </div>
                                <div class="control">
                                    <div class="button is-borderless is-light">
                                        <input id="all" :checked="toBeMergedKeys.length" type="checkbox" class="cbx-checkbox" @click="wrapAll()">
                                        <label for="all" class="cbx is-marginless">
                                            <svg width="14px" height="12px" viewBox="0 0 14 12"><polyline points="1 7.6 5 11 13 1"/></svg>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </th>
                        <th class="is-link" width="1%">{{ trans('key') }}</th>
                        <th v-for="(l, i) in locales" :key="i" class="is-link">
                            <div class="tags has-addons is-marginless">
                                <span class="tag is-marginless link is-light is-medium">{{ l }}</span>
                                <span class="tag is-marginless link is-warning is-medium" @click="removeLocale(l)">
                                    <icon name="trash"/>
                                </span>
                            </div>
                        </th>
                        <th class="is-link">{{ trans('ops') }}</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="(mainV, mainK, mainI) in selectedFileDataClone"
                        v-if="inList(mainK)"
                        :key="mainI">

                        <!-- merge -->
                        <td style="text-align: center;">
                            <input :id="mainK" :value="mainK"
                                   v-model="toBeMergedKeys"
                                   type="checkbox"
                                   class="cbx-checkbox">
                            <label :for="mainK" class="cbx is-marginless">
                                <svg width="14px" height="12px" viewBox="0 0 14 12"><polyline points="1 7.6 5 11 13 1"/></svg>
                            </label>
                        </td>

                        <!-- key -->
                        <td v-tippy="{position : 'right', arrow: true, interactive: true, trigger: 'mouseenter'}" :title="getKey(mainK)" :class="{'nestedKeys' : mainK.includes('.')}"
                            :data-main-key="mainK"
                            nowrap
                            contenteditable
                            dir="auto"

                            data-html="#tippyTemplate"
                            @mouseenter="keyToCopy = getKey(mainK)"
                            @keydown.enter.prevent
                            @input="newEntry()"
                            @blur="saveNewKey($event)">
                            {{ mainK }}
                        </td>

                        <!-- value -->
                        <td v-for="(nestV, nestK, nestI) in mainV" :key="nestI"
                            :data-main-key="mainK" :data-code="nestK"
                            contenteditable
                            dir="auto"
                            @input="newEntry()"
                            @blur="saveNewValue($event)">
                            {{ nestV }}
                        </td>

                        <td width="1%">
                            <!-- del -->
                            <button class="button is-danger" @click="removeItem(mainK)">
                                <span class="icon"><icon name="trash"/></span>
                            </button>
                            <!-- clone -->
                            <button class="button is-primary" @click="copyItem(mainK, mainV)">
                                <span class="icon"><icon name="clone" scale="0.8"/></span>
                            </button>
                        </td>
                    </tr>

                    <!-- nothing found -->
                    <tr v-if="dontHaveData()">
                        <td :colspan="locales.length + 3" style="text-align: center">
                            {{ trans('no_data') }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- ops -->
            <div class="level">
                <div class="level-left">
                    <!-- add new item -->
                    <div class="level-item">
                        <button class="button is-link" @click.prevent="addNewItem()">
                            {{ trans('add_new') }}
                        </button>
                    </div>

                    <!-- add copied -->
                    <div class="level-item">
                        <button :disabled="!copiedItem" class="button" @click.prevent="addCopiedItem()">
                            {{ trans('add_copied') }}
                        </button>
                    </div>
                </div>

                <div class="level-right">
                    <!-- save changes -->
                    <div class="level-item">
                        <button :disabled="!dataChanged" class="button is-success" @click="submitNewData()">
                            {{ trans('save') }}
                        </button>
                    </div>
                    <!-- reset -->
                    <div class="level-item">
                        <button :disabled="!dataChanged" class="button is-warning" @click="resetData()">
                            {{ trans('reset') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- tippy template -->
            <div id="tippyTemplate">
                <span class="c2c">{{ keyToCopy }}</span>
            </div>
        </section>

        <!-- modal -->
        <div :class="{'is-active': showModal}"
             class="modal animated fadeIn">
            <div class="modal-background link" @click="toggleModal()"/>
            <div class="modal-card animated fadeInDown">
                <header class="modal-card-head">
                    <p class="modal-card-title"><span>{{ trans('merge_keys') }}</span></p>
                    <button type="button" class="delete" @click="toggleModal()"/>
                </header>
                <section class="modal-card-body">
                    <input v-model="mergerName" class="input" type="text" placeholder="keyName" autofocus>
                </section>

                <footer class="modal-card-foot">
                    <button type="reset" class="button" @click="toggleModal()">{{ trans('cancel') }}</button>
                    <button type="submit" class="button is-success" @click="mergeKeys()">{{ trans('save') }}</button>
                </footer>
            </div>
        </div>
    </div>
</template>

<style scoped>
    #tippyTemplate {
        display: none;
    }

    .c2c {
        cursor: pointer;
    }
</style>

<script>
import Forms from './forms'

export default{
    name: 'shared-content',
    mixins: [Forms],
    data() {
        return this.$parent.$data
    },
    updated() {
        this.tableColumnResize()
    },
    computed: {
        itemsCount() {
            return this.filteredList().length
        }
    },
    methods: {
        // search
        keysList() {
            return Object.keys(this.selectedFileDataClone)
        },
        filteredList() {
            return this.keysList().filter((e) => {
                return e.includes(this.searchFor.toLowerCase())
            })
        },
        inList(key) {
            return this.filteredList().includes(key)
        },
        resetSearch() {
            this.searchFor = ''
        },

        // table ops
        tableColumnResize() {
            let el
            let startOffset

            document.querySelectorAll('table th').forEach((th) => {
                th.style.position = 'sticky'

                let grip = document.createElement('div')
                grip.innerHTML = '&nbsp;'
                grip.style.top = 0
                grip.style.right = 0
                grip.style.bottom = 0
                grip.style.width = '10px'
                grip.style.position = 'absolute'
                grip.style.cursor = 'col-resize'
                grip.addEventListener('mousedown', (e) => {
                    el = th
                    startOffset = th.offsetWidth - e.pageX
                })

                th.appendChild(grip)
            })

            document.addEventListener('mousemove', (e) => {
                if (el) {
                    el.style.width = startOffset + e.pageX + 'px'
                }
            })

            document.addEventListener('mouseup', () => {
                el = undefined
            })
        },

        // ops
        copyItem(key, val) {
            this.copiedItem = {[key]: val}
        },
        addCopiedItem() {
            this.dataChanged = true

            let item = this.copiedItem
            let fileData = this.selectedFileDataClone
            let locales = this.locales
            let key = Object.keys(item)[0]
            let val = Object.values(item)[0]

            // exist
            if (this.keysList().some((e) => e == key)) {
                return this.showNotif(this.trans('copied_key_exist'), 'warning')
            }

            // equalize locales "TEMP RESTRICTION"
            if (Object.keys(val).length !== locales.length) {
                return this.showNotif(this.trans('copied_key_length'), 'danger')
            }

            // more to less
            // issues : old copied item changes after paste & copying a new one
            //
            // Object.keys(val).forEach((code) => {
            //     if (!locales.includes(code)) {
            //         delete val[code]
            //     }
            // })

            // less to more
            // issues : wrong render of locales value in table while its correct
            //
            // locales.forEach((code) => {
            //     if (!val.hasOwnProperty(code)) {
            //         return val[code] = ''
            //     }
            // })

            return this.$set(fileData, key, val)
        },
        addNewItem() {
            let name = 'newItem' + this.newItemCounter
            let fileData = this.selectedFileDataClone

            this.dataChanged = true

            // incase we already have a key == name
            if (fileData.hasOwnProperty(name)) {
                this.newItemCounter++
                return this.addNewItem()
            }

            this.locales.forEach((code) => {
                // name not added yet
                if (!fileData.hasOwnProperty(name)) {
                    this.$set(fileData, name, {[code]: ''})
                }
                // name is added but not the code
                else {
                    this.$set(fileData[name], code, '')
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
            this.selectedFileDataClone = JSON.parse(JSON.stringify(this.selectedFileData))
            this.parentMethod('resetAll', ['keyToCopy', 'newKeys'])
        },

        // keys merger
        wrapAll() {
            if (this.toBeMergedKeys.length) {
                return this.toBeMergedKeys = []
            }

            this.toBeMergedKeys = this.searchFor
                ? this.filteredList()
                : this.keysList()
        },
        mergeKeys() {
            this.toBeMergedKeys.map((old_key) => {
                let new_key = `${this.mergerName}.${old_key}`

                this.newKeyOps(old_key, new_key)
            })

            this.toggleModal()
            this.submitNewData()
            this.wrapAll()
        },
        toggleModal(val = false) {
            if (val && this.dataChanged) {
                return this.showNotif(this.trans('merge_warning'), 'warning')
            }

            this.showModal = val
        },

        // util
        newEntry() {
            this.dataChanged = true
        },
        saveNewKey(e) {
            let text = e.target.innerText = e.target.innerText.toLowerCase().replace(/\s/g, '_')
            let old_key = e.target.dataset.mainKey
            let new_key = text

            this.newKeyOps(old_key, new_key)
        },
        newKeyOps(old_key, new_key) {
            if (old_key !== new_key) {
                this.dataChanged = true

                if (this.newKeys) {
                    return this.newKeys[old_key] = new_key
                }

                this.newKeys = {[old_key]: new_key}
            }
        },
        saveNewValue(e) {
            let code = e.target.dataset.code
            let key = e.target.dataset.mainKey
            let text = e.target.innerText = e.target.innerText.replace(/\n/g, '<br>')
            let value = text

            if (this.selectedFileDataClone[key][code] !== value) {
                this.dataChanged = true
                this.$set(this.selectedFileDataClone[key], code, value)
            }
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

        // other
        dontHaveData() {
            return Object.keys(this.selectedFileDataClone).length == 0
        },

        // parent
        getKey(key) {
            return this.parentMethod('getKey', key)
        },
        trans(key) {
            return this.parentMethod('trans', key)
        },
        failedAjax() {
            return this.parentMethod('failedAjax')
        },
        showNotif(msg, s = 'success') {
            return this.$parent.showNotif(msg, s)
        },
        parentMethod(method_name, args = null) {
            return this.$parent[method_name](args)
        }
    }
}
</script>
