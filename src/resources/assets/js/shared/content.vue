<template>
    <div>
        <div class="level">
            <!-- items count -->
            <div class="level-left">
                <div class="level-item">
                    <div class="field is-grouped is-grouped-left">
                        <div class="control" v-if="selectedFile">
                            <h4 class="title is-4">"{{ itemsCount }}" Items/s</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- select file -->
            <div class="level-right">
                <div class="level-item">
                    <div class="field is-grouped is-grouped-right" v-if="files.length">
                        <div class="control has-icons-left">
                            <div class="select">
                                <select v-model="selectedFile">
                                    <option value="" disabled>{{ trans('select_file') }}</option>
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
                </div>
            </div>
        </div>

        <!-- data -->
        <section v-if="selectedFile" class="m-t-50">
            <!-- table -->
            <table class="table is-fullwidth is-bordered">
                <thead>
                    <tr class="is-unselectable">
                        <th width="1%">{{ trans('key') }}</th>
                        <th v-for="(l, i) in locales" :key="i">
                            <div class="tags has-addons">
                                <span class="tag is-dark is-medium">{{ l }}</span>
                                <span class="tag is-warning is-medium" @click="removeLocale(l)">
                                    <i class="fa fa-trash"></i>
                                </span>
                            </div>
                        </th>
                        <th>{{ trans('ops') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(mainV, mainK, mainI) in selectedFileDataClone" :key="mainI">
                        <td nowrap width="1%" :class="nestCheck(mainK)">
                            <div style="visibility: hidden; height: 1px;">{{ mainK }}</div>
                            <textarea rows="1"
                                dir="auto"
                                :class="nestCheck(mainK)"
                                :data-main-key="mainK"
                                v-html="mainK"
                                @keydown.enter.prevent
                                @input="saveNewKey($event)">
                            </textarea>
                        </td>
                        <td v-for="(nestV, nestK, nestI) in mainV" :key="nestI">
                            <textarea rows="1"
                                dir="auto"
                                :data-code="nestK"
                                :data-main-key="mainK"
                                v-html="nestV"
                                @keydown.enter.prevent
                                @input="saveNewValue($event)">
                            </textarea>
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
                            {{ trans('no_data') }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- ops -->
            <div class="level">
                <div class="level-right">
                    <div class="level-item">
                        <button class="button is-link" @click.prevent="addNewItem()">
                            {{ trans('add_new') }}
                        </button>
                    </div>
                </div>
                <div class="level-left">
                    <div class="level-item">
                        <button class="button is-success" :disabled="!dataChanged" @click="submitNewData()">
                            {{ trans('save') }}
                        </button>
                    </div>
                    <div class="level-item">
                        <button class="button" :disabled="!dataChanged" @click="resetData()">
                            {{ trans('reset') }}
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
    .tag {
        border-radius: 3px;
    }
</style>

<script>
import Forms from './forms'

export default{
    name: 'content',
    mixins: [Forms],
    data() {
        return this.$parent.$data
    },
    computed: {
        itemsCount() {
            return Object.keys(this.selectedFileDataClone).length
        }
    },
    updated() {
        this.tableFloatHead($('table'), $('#menu').outerHeight(true))
        this.tableColumnResize()
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

            this.parentMethod('reflowTable')
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
                    this.parentMethod('reflowTable')
                    thElm.style.width = startOffset + e.pageX + 'px'
                }
            })

            document.addEventListener('mouseup', () => {
                thElm = undefined
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

                return this.parentMethod('resetAll', ['newKeys', 'selectedFile'])
            }

            this.selectedFileDataClone = Object.assign({}, this.selectedFileData)
        },

        // util
        saveNewKey(e) {
            this.parentMethod('reflowTable')

            let old_key = e.target.dataset.mainKey
            let new_key = e.target.value

            this.dataChanged = true

            if (this.newKeys) {
                return this.newKeys[old_key] = new_key
            }

            this.newKeys = {[old_key] : new_key}
        },
        saveNewValue(e) {
            this.parentMethod('reflowTable')

            let code = e.target.dataset.code
            let key = e.target.dataset.mainKey
            let value = e.target.value

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

        // other
        nestCheck(item) {
            return item.includes('.') ? 'nestedKey' : ''
        },
        dontHaveData() {
            return Object.keys(this.selectedFileDataClone).length == 0
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
