<template>
    <div>
        <!-- select file -->
        <div class="field is-grouped is-grouped-right" v-if="files.length">
            <div class="control has-icons-left">
                <div class="select">
                    <select v-model="selectedFile">
                        <option value="" disabled>{{ trans('select_file') }}</option>
                        <option v-for="(f, i) in files" :key="i">{{ f }}</option>
                    </select>
                </div>
                <div class="icon is-small is-left"><icon name="file"/></div>
            </div>
            <div class="control" v-if="selectedFile">
                <button class="button is-danger" @click="removeSelectedFile()">
                    <span class="icon">
                        <icon name="trash"/>
                    </span>
                </button>
            </div>
        </div>

        <div class="level is-mobile is-marginless m-t-40" v-if="selectedFile">
            <!-- items count -->
            <div class="level-left">
                <div class="level-item">
                    <div class="control">
                        <h4 class="title is-4">"{{ itemsCount }}" Item/s</h4>
                    </div>
                </div>
            </div>

            <div class="level-right">
                <div class="level-item">
                    <!-- search -->
                    <div class="field has-addons">
                        <p class="control has-icons-left">
                            <input class="input"
                                   type="text"
                                   v-model="searchFor"
                                   :placeholder="trans('find')">
                            <span class="icon is-left">
                                <icon name="search"/>
                            </span>
                        </p>
                        <p class="control">
                            <button class="button is-black" :disabled="!searchFor"
                                    @click="resetSearch()">
                                <span class="icon"><icon name="times"/></span>
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- data -->
        <section v-if="selectedFile" class="m-t-10">
            <!-- table -->
            <table class="table is-fullwidth is-hoverable is-bordered">
                <thead>
                    <tr class="is-unselectable">
                        <th class="is-link" width="1%">
                            <div class="field has-addons is-marginless">
                                <div class="control">
                                    <button class="button is-borderless" :disabled="toBeMergedKeys.length < 2" @click="toggleModal(true)">
                                        {{ trans('merge_keys') }}
                                    </button>
                                </div>
                                <div class="control">
                                    <div class="button is-borderless is-light">
                                        <input type="checkbox" id="all" class="cbx-checkbox" @click="wrapAll()" :checked="toBeMergedKeys.length">
                                        <label for="all" class="cbx is-marginless">
                                            <svg width="14px" height="12px" viewBox="0 0 14 12"><polyline points="1 7.6 5 11 13 1"/></svg>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </th>
                        <th class="is-link" width="1%">{{ trans('key') }}</th>
                        <th class="is-link" v-for="(l, i) in locales" :key="i">
                            <div class="tags has-addons">
                                <span class="tag is-light is-medium">{{ l }}</span>
                                <span class="tag is-warning is-medium" @click="removeLocale(l)">
                                    <icon name="trash"/>
                                </span>
                            </div>
                        </th>
                        <th class="is-link">{{ trans('ops') }}</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="(mainV, mainK, mainI) in selectedFileDataClone"
                        :key="mainI"
                        v-if="inList(mainK)">

                        <!-- merge -->
                        <td style="text-align: center;">
                            <input type="checkbox" :id="mainK"
                                   class="cbx-checkbox"
                                   :value="mainK"
                                   v-model="toBeMergedKeys">
                            <label :for="mainK" class="cbx is-marginless">
                                <svg width="14px" height="12px" viewBox="0 0 14 12"><polyline points="1 7.6 5 11 13 1"/></svg>
                            </label>
                        </td>

                        <!-- key -->
                        <td nowrap contenteditable dir="auto"
                            :title="getKey(mainK)"
                            v-tippy="{position : 'right', arrow: true, interactive: true, trigger: 'mouseenter'}"
                            data-html="#tippyTemplate"
                            @mouseenter="keyToCopy = getKey(mainK)"

                            :class="{'nestedKeys' : mainK.includes('.')}"
                            :data-main-key="mainK"
                            @keydown.enter.prevent
                            @input="newEntry()"
                            @blur="saveNewKey($event)">
                            {{ mainK }}
                        </td>

                        <!-- value -->
                        <td v-for="(nestV, nestK, nestI) in mainV" :key="nestI"
                            contenteditable dir="auto"
                            :data-main-key="mainK"
                            :data-code="nestK"
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
                            <button class="button is-primary" @click="copyItem({[mainK]: mainV})">
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
                        <button class="button" @click.prevent="addCopiedItem()" :disabled="!copiedItem">
                            {{ trans('add_copied') }}
                        </button>
                    </div>
                </div>

                <div class="level-right">
                    <!-- reset -->
                    <div class="level-item">
                        <button class="button is-warning" :disabled="!dataChanged" @click="resetData()">
                            {{ trans('reset') }}
                        </button>
                    </div>
                    <!-- save changes -->
                    <div class="level-item">
                        <button class="button is-success" :disabled="!dataChanged" @click="submitNewData()">
                            {{ trans('save') }}
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
        <div class="modal animated fadeIn"
             :class="{'is-active': showModal}">
            <div class="modal-background link" @click="toggleModal()"/>
            <div class="modal-card animated fadeInDown">
                <header class="modal-card-head">
                    <p class="modal-card-title"><span>{{ trans('merge_keys') }}</span></p>
                    <button type="button" class="delete" @click="toggleModal()"/>
                </header>
                <section class="modal-card-body">
                    <input class="input" type="text" v-model="mergerName" placeholder="keyName" autofocus>
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
    computed: {
        itemsCount() {
            return this.filteredList().length
        }
    },
    updated() {
        this.tableColumnResize()
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
        copyItem(item) {
            this.copiedItem = item
        },
        addCopiedItem() {
            let item = this.copiedItem
            let fileData = this.selectedFileDataClone

            let key = Object.keys(item)[0]
            let val = Object.values(item)[0]
            let test = this.keysList().some((e) => {
                return e == key
            })

            console.log(val, Object.values(val).length)

            if (test) {
                return this.showNotif(this.trans('copied_key_exist'), 'warning')
            }

            if (Object.values(val).length !== this.locales.length) {
                return this.showNotif(this.trans('copied_key_length'), 'danger')
            }

            this.$set(fileData, key, val)
            this.dataChanged = true
        },
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
                    this.$set(fileData, name, {[item]: ''})
                } else {
                    this.$set(fileData[name], item, '')
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
        showNotif(msg, s = 'success') {
            return this.$parent.showNotif(msg, s)
        },
        getKey(key) {
            return this.parentMethod('getKey', key)
        },
        trans(key) {
            return this.parentMethod('trans', key)
        },
        parentMethod(method_name, args = null) {
            return this.$parent[method_name](args)
        }
    }
}
</script>
