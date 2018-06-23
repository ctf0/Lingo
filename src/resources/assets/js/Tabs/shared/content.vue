<template>
    <div>
        <section class="sticky-section">
            <slot/>

            <div class="level">
                <!-- add copied -->
                <div class="level-left">
                    <transition name="slide-fade">
                        <div v-if="selectedFile" class="level-item">
                            <button :disabled="!copiedItem" class="button" @click.prevent="addCopiedItem()">
                                {{ trans('add_copied') }}
                            </button>
                        </div>
                    </transition>
                </div>

                <!-- select file -->
                <div class="level-right">
                    <transition name="slide-fade">
                        <div v-if="showSection()" class="field is-grouped is-grouped-right">
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
                                <!-- remove -->
                                <button class="button is-danger" @click="removeSelectedFile()">
                                    <span class="icon"><icon name="trash"/></span>
                                </button>

                                <!-- download -->
                                <form :action="routes.downloadFileRoute" method="get" class="is-inline-block">
                                    <input v-if="hasDirs()" :value="selectedDir" type="hidden" name="dir_name">
                                    <input :value="selectedFile" type="hidden" name="file_name">
                                    <button type="submit" class="button is-outlined">
                                        <span class="icon"><icon name="download"/></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>

            <transition name="slide-fade">
                <div v-if="selectedFile && selectedFileDataClone" class="level is-mobile is-marginless m-t-40">
                    <!-- items count -->
                    <div class="level-left">
                        <div class="level-item">
                            <h4 class="title is-4">"{{ itemsCount }}" {{ trans('items') }}</h4>
                        </div>
                        <transition name="slide-fade">
                            <div v-if="itemsCount" class="level-item">
                                <scroll/>
                            </div>
                        </transition>
                    </div>

                    <div class="level-right">
                        <div class="level-item is-marginless">
                            <!-- search -->
                            <div class="field has-addons">
                                <p class="control has-icons-left">
                                    <input ref="search"
                                           :placeholder="trans('find')"
                                           class="input"
                                           type="text"
                                           @input="debounceInput">
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
            </transition>
        </section>

        <!-- data -->
        <transition name="slide-fade">
            <section v-if="selectedFile">
                <!-- table -->
                <table :style="THeadTop" class="table is-fullwidth is-hoverable is-bordered">
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
                            <th class="is-link link" width="1%" @click="sortBy('key')">{{ trans('key') }}</th>
                            <th v-for="(l, i) in locales" :key="i" class="is-link link" @click="sortBy(l)">
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
                        <tr v-for="(item, i) in filteredList" :key="i">

                            <!-- merge -->
                            <td style="text-align: center;">
                                <input :id="item.name"
                                       :value="item.name"
                                       v-model="toBeMergedKeys"
                                       type="checkbox"
                                       class="cbx-checkbox">
                                <label :for="item.name" class="cbx is-marginless">
                                    <svg width="14px" height="12px" viewBox="0 0 14 12"><polyline points="1 7.6 5 11 13 1"/></svg>
                                </label>
                            </td>

                            <!-- key -->
                            <td v-tippy="{position : 'right', arrow: true, interactive: true, trigger: 'mouseenter'}"
                                :title="getKey(item.name)"
                                :class="{'nestedKeys' : item.name && item.name.includes('.')}"
                                :data-main-key="item.name"
                                :ref="`td-${item.name}`"
                                nowrap
                                contenteditable
                                dir="auto"

                                data-html="#tippyTemplate"
                                @mouseenter="keyToCopy = getKey(item.name)"
                                @keydown.enter.prevent
                                @input="newEntry()"
                                @blur="saveNewKey($event)">
                                {{ item.name }}
                            </td>

                            <!-- value -->
                            <td v-for="(codeV, codeK) in item.locales" :key="codeK"
                                :data-main-key="item.name"
                                :data-code="codeK"
                                contenteditable
                                dir="auto"
                                @input="newEntry()"
                                @blur="saveNewValue($event)">
                                {{ codeV }}
                            </td>

                            <td width="1%">
                                <!-- del -->
                                <button v-tippy
                                        :title="trans('delete')"
                                        class="button is-danger"
                                        @click="removeItem(i)">
                                    <span class="icon"><icon name="trash"/></span>
                                </button>
                                <!-- clone -->
                                <button v-tippy
                                        :title="trans('copy')"
                                        class="button is-primary"
                                        @click="copyItem(item)">
                                    <span class="icon"><icon name="clone" scale="0.8"/></span>
                                </button>
                            </td>
                        </tr>

                        <!-- nothing found -->
                        <tr v-if="noData()">
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
        </transition>

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

    th {
        top: var(--tHead);
    }
</style>

<script>
import Forms from './forms'
import Scroll from './Scroll/btns.vue'

import orderBy from 'lodash/orderBy'
import debounce from 'lodash/debounce'
import cloneDeep from 'lodash/cloneDeep'

export default{
    components: {Scroll},
    name: 'shared-content',
    mixins: [Forms],
    data() {
        return this.$parent.$data
    },
    mounted() {
        // copy to clipboard
        document.body.onclick = (e) => {
            e = window.event ? e.srcElement : e.target
            if (e.classList.contains('c2c')) {
                this.$copyText(this.keyToCopy)
            }
        }
    },
    updated() {
        this.tableColumnResize()
    },
    computed: {
        keysList() {
            if (this.itemsCount) {
                return this.filteredList.map((e) => {
                    return e.name
                })
            }

            return []
        },
        filteredList() {
            let val = this.searchFor

            if (val) {
                return new Fuse(this.selectedFileDataClone, {
                    keys: ['name'],
                    threshold: 0.3
                }).search(val)
            }

            return this.selectedFileDataClone || []
        },
        itemsCount() {
            return this.filteredList.length
        }
    },
    methods: {
        showSection() {
            return this.dirs &&
                this.dirs.length &&
                this.selectedDir || this.files.length
        },
        hasDirs() {
            return this.dirs && this.dirs.length && this.selectedDir
        },

        // search
        debounceInput: debounce(function({target}) {
            this.searchFor = target.value
        }, 250),
        resetSearch() {
            this.$refs.search.value = ''
            this.searchFor = ''
        },

        // table ops
        tableColumnResize() {
            let el
            let startOffset

            document.querySelectorAll('table th:not(:last-of-type)').forEach((th) => {
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
        sortBy(item) {
            let dir = this.orderDirection == 'asc' ? 'desc' : 'asc'

            if (item == 'key') {
                this.selectedFileDataClone = orderBy(this.selectedFileDataClone, ['name'], [dir])
                return this.orderDirection = dir
            }

            this.selectedFileDataClone = orderBy(this.selectedFileDataClone, [`locales.${item}`], [dir])
            return this.orderDirection = dir
        },

        // ops
        copyItem(item) {
            this.copiedItem = item
        },
        addCopiedItem() {
            this.newEntry()

            let item = cloneDeep(this.copiedItem)
            let val = item.locales
            let fileData = this.selectedFileDataClone
            let locales = this.locales

            // exist
            if (fileData.some((e) => e.name == item.name)) {
                this.searchFor = item.name
                this.$refs.search.value = item.name
                this.$refs.search.focus()
                return this.showNotif(this.trans('copied_key_exist'), 'warning')
            }

            // more to less
            Object.keys(val).forEach((code) => {
                if (!locales.includes(code)) {
                    delete val[code]
                }
            })

            // less to more
            locales.forEach((code) => {
                if (!val.hasOwnProperty(code)) {
                    return val[code] = ''
                }
            })

            // make sure the locales are in correct order
            item.locales = Object.keys(val).sort().reduce((r, k) => (r[k] = val[k], r), {})

            fileData.push(item)
            this.scrollToBottom()
        },
        addNewItem() {
            this.newEntry()
            this.resetSearch()

            let name = 'newItem' + this.newItemCounter
            let fileData = this.selectedFileDataClone
            let test = this.keysList.includes(name)

            // incase we already have a key == name
            if (test) {
                this.newItemCounter++
                return this.addNewItem()
            }

            let lc = {}
            this.locales.forEach((e) => {
                lc[e] = ''
            })

            fileData.push({
                name: name,
                locales: lc
            })

            this.$nextTick(() => {
                this.scrollToBottom()
            })

            this.newItemCounter++
        },
        removeItem(index) {
            this.newEntry()
            this.selectedFileDataClone.splice(index, 1)
        },
        resetData() {
            this.dataChanged = false
            this.newItemCounter = 0

            // because any changes in DOM are not reseted
            this.selectedFileDataClone = {}
            this.$nextTick(() => {
                this.selectedFileDataClone = cloneDeep(this.selectedFileData)
            })

            this.parentMethod('resetAll', ['keyToCopy', 'newKeys'])
        },

        // keys merger
        wrapAll() {
            if (this.toBeMergedKeys.length) {
                return this.toBeMergedKeys = []
            }

            this.toBeMergedKeys = this.keysList
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
                this.newEntry()

                if (this.newKeys) {
                    return this.newKeys[old_key] = new_key
                }

                this.newKeys = {[old_key]: new_key}
            }
        },
        saveNewValue(e) {
            let code = e.target.dataset.code
            let key = e.target.dataset.mainKey
            let value = e.target.innerText = e.target.innerText.replace(/\n/g, '<br>')
            let fileData = this.selectedFileDataClone

            fileData.some((e, i) => {
                if (e.name == key && e.locales[code] !== value) {
                    this.newEntry()
                    fileData[i].locales[code] = value
                }
            })
        },

        // other
        noData() {
            return this.itemsCount == 0
        },
        scrollToBottom() {
            document.querySelector('.toDown').click()
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
