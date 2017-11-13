<template>
    <div>
        <div class="level is-mobile is-marginless">
            <!-- items count -->
            <div class="level-left">
                <div class="level-item">
                    <div class="field is-grouped is-grouped-left">
                        <div class="control" v-if="selectedFile">
                            <h4 class="title is-4">"{{ itemsCount }}" Item/s</h4>
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
                        <td nowrap contenteditable dir="auto"
                            :title="getKey(mainK)"
                            v-tippy="{ position : 'right',  arrow: true, interactive: true, trigger: 'mouseenter'}"
                            @shown="refocus()"
                            data-html="#tippyTemplate"
                            @mouseenter="keyToCopy = getKey(mainK)"

                            :class="nestCheck(mainK)"
                            :data-main-key="mainK"
                            @keydown.enter.prevent
                            @input="newEntry()"
                            @focus="getPos($event)"
                            @blur="saveNewKey($event)">
                            {{ mainK }}
                        </td>

                        <td v-for="(nestV, nestK, nestI) in mainV" :key="nestI"
                            contenteditable dir="auto"
                            :data-main-key="mainK"
                            :data-code="nestK"
                            @input="newEntry()"
                            @focus="getPos($event)"
                            @blur="saveNewValue($event)">
                            {{ nestV }}
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

            <!-- tippy template -->
            <div id="tippyTemplate">
                <span class="c2c">{{ keyToCopy }}</span>
            </div>
        </section>
    </div>
</template>

<style scoped>
    .tag {
        border-radius: 3px;
    }

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
        this.tableFloatHead($('table'), 0)
        this.tableColumnResize()
    },
    methods: {
        // table ops
        tableFloatHead(table, offset) {
            setTimeout(() => {
                table.floatThead({
                    top: offset,
                    autoReflow: true,
                    responsiveContainer(table) {
                        return table.closest('section')
                    }
                })
            }, 600)

            this.parentMethod('reflowTable')
        },
        tableColumnResize() {
            let el
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
                    el = th
                    startOffset = th.offsetWidth - e.pageX
                })

                th.appendChild(grip)
            })

            document.addEventListener('mousemove', (e) => {
                if (el) {
                    this.parentMethod('reflowTable')
                    el.style.width = startOffset + e.pageX + 'px'
                }
            })

            document.addEventListener('mouseup', () => {
                el = undefined
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
            this.selectedFileDataClone = JSON.parse(JSON.stringify(this.selectedFileData))

            // hacky fix as newkeys dont reset
            if (this.newKeys) {
                let old = this.selectedFile

                setTimeout(() => {
                    this.selectedFile = old
                    this.parentMethod('reflowTable')
                }, 10)

                this.parentMethod('resetAll', ['selectedFile'])
            }

            this.parentMethod('resetAll', ['keyToCopy', 'currentInputRef'])
        },

        // util
        newEntry() {
            this.dataChanged = true
            this.parentMethod('reflowTable')
        },
        saveNewKey(e) {
            let old_key = e.target.dataset.mainKey
            let text = e.target.innerText = e.target.innerText.toLowerCase().replace(/\s/g, '_')
            let new_key = text

            if (old_key !== new_key) {
                this.dataChanged = true

                if (this.newKeys) {
                    return this.newKeys[old_key] = new_key
                }

                this.newKeys = {[old_key] : new_key}
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

        // tippy & ctcp
        getPos(e) {
            this.currentInputRef = e
        },
        refocus() {
            return this.parentMethod('refocus')
        },
        getKey(key) {
            return this.parentMethod('getKey', key)
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
