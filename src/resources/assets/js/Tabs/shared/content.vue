<template>
    <div>
        <section class="sticky-section">
            <slot/>

            <div class="level">
                <!-- add copied -->
                <div class="level-left">
                    <transition name="lin-slide-fade">
                        <div v-if="selectedFile" class="level-item">
                            <button :disabled="!copiedItem" class="button" @click.prevent="addCopiedItem()">
                                {{ trans('add_copied') }}
                            </button>
                        </div>
                    </transition>
                </div>

                <!-- select file -->
                <div class="level-right">
                    <transition name="lin-slide-fade">
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

            <transition name="lin-slide-fade">
                <div v-if="selectedFile && selectedFileDataClone" class="level is-mobile is-marginless m-t-40">
                    <!-- items count -->
                    <div class="level-left">
                        <div class="level-item">
                            <h4 class="title is-4">"{{ itemsCount }}" {{ trans('items') }}</h4>
                        </div>
                        <transition name="lin-slide-fade">
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
                                           :value="searchFor"
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
        <transition name="lin-slide-fade">
            <section v-if="selectedFile">
                <!-- table -->
                <table :style="THeadTop" class="table is-fullwidth is-hoverable is-bordered">
                    <thead>
                        <tr class="is-unselectable">
                            <th class="is-link static-cell">
                                <div class="field has-addons is-marginless">
                                    <div class="control">
                                        <button :disabled="mergerKeysCount < 2" class="button is-borderless" @click="toggleModal(true)">
                                            {{ trans('update') }}
                                        </button>
                                    </div>

                                    <div class="control">
                                        <div class="button is-borderless is-light">
                                            <input id="all" :checked="mergerKeysCount" type="checkbox" class="cbx-checkbox" @click="wrapAll()">
                                            <label for="all" class="cbx is-marginless">
                                                <svg width="14px" height="12px" viewBox="0 0 14 12"><polyline points="1 7.6 5 11 13 1"/></svg>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </th>
                            <th class="is-link link static-cell" @click="sortBy('key')">{{ trans('key') }}</th>
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

                    <transition-group tag="tbody" name="lin-slide-fade" mode="out-in">
                        <tr v-for="(item, i) in filteredList" :key="item.name">

                            <!-- merge -->
                            <td class="has-text-centered link" @click="clickOnCkBox(`item-${i}`)">
                                <input :id="`item-${i}`"
                                       :value="item.name"
                                       v-model="keysToBeMerged"
                                       type="checkbox"
                                       class="cbx-checkbox">
                                <label :for="`item-${i}`" class="cbx is-marginless" @click.prevent>
                                    <svg width="14px" height="12px" viewBox="0 0 14 12"><polyline points="1 7.6 5 11 13 1"/></svg>
                                </label>
                            </td>

                            <!-- key -->
                            <td v-tippy="{placement: 'right', arrow: true, interactive: true}"
                                :title="getTTC(getKey(item.name), item.locales)"
                                :class="{'nestedKeys' : item.name && item.name.includes('.')}"
                                :data-main-key="item.name"
                                class="static-cell"
                                contenteditable
                                dir="auto"
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

                            <td class="static-cell">
                                <!-- del -->
                                <button v-tippy
                                        :title="trans('delete')"
                                        class="button is-danger"
                                        @click="removeItem(item.name, i)">
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
                        <tr v-if="noData()" key="noData">
                            <td :colspan="locales.length + 3" class="has-text-centered">
                                {{ trans('no_data') }}
                            </td>
                        </tr>
                    </transition-group>
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
            </section>
        </transition>

        <!-- modal -->
        <div :class="{'is-active': showModal}"
             class="modal lin-animated fadeIn">
            <div class="modal-background link" @click="toggleModal()"/>
            <div class="modal-card lin-animated fadeInDown">
                <header class="modal-card-head">
                    <p class="modal-card-title"><span>{{ trans('update') }}</span></p>
                    <button type="button" class="delete" @click="toggleModal()"/>
                </header>

                <section class="modal-card-body">
                    <h3 class="title is-4 is-marginless">{{ trans('to_be_merged') }}: "{{ mergerKeysCount }}"</h3>
                    <table class="table diff is-narrow">
                        <tr v-for="item in keysToBeMerged" :key="item">
                            <td>{{ item }}</td>
                            <td><span class="icon"><icon name="arrow-right" scale="0.8"/></span></td>
                            <td>{{ getUpdatedKey(item) }}</td>
                        </tr>
                    </table>

                    <input v-model="mergerName" class="input" type="text" placeholder="keyName" autofocus>
                </section>

                <footer class="modal-card-foot">
                    <div class="level full-width">
                        <div class="level-left">
                            <div class="level-item">
                                <div class="form-switcher">
                                    <input id="use_replace" v-model="useReplace" type="checkbox">
                                    <label class="switcher" for="use_replace"/>
                                </div>
                            </div>
                            <div class="level-item">
                                <label class="label" for="use_replace">Replace instead ?</label>
                            </div>
                        </div>

                        <div class="level-right">
                            <div class="level-item">
                                <button type="reset" class="button" @click="toggleModal()">{{ trans('cancel') }}</button>
                            </div>
                            <div class="level-item">
                                <button type="submit" class="button is-success" @click="mergeKeys()">{{ trans('save') }}</button>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
    .diff {
        margin-left: 2rem;
        margin-top: 1rem;
        border-left: 5px solid #00d1b2;

        td {
            border: none;
            text-align: left !important;
        }
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
        document.addEventListener('click', this.onClick)
        document.addEventListener('keydown', this.onKeydown)
    },
    updated() {
        this.tableColumnResize()
    },
    beforeDestroy() {
        document.removeEventListener('click', this.onClick)
        document.removeEventListener('keydown', this.onKeydown)
    },
    computed: {
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
        },
        keysList() {
            if (this.itemsCount) {
                return this.filteredList.map((e) => {
                    return e.name
                })
            }

            return []
        },
        mergerKeysCount() {
            return this.keysToBeMerged.length
        }
    },
    methods: {
        showSection() {
            if (this.dirs) {
                return this.dirs &&
                    this.dirs.length &&
                    (this.selectedDir || this.files.length)
            }

            return this.files.length
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
        removeItem(name, index) {
            this.newEntry()

            if (this.searchFor) {
                return this.selectedFileDataClone.some((e, i) => {
                    if (e.name == name) {
                        this.selectedFileDataClone.splice(i, 1)
                    }
                })
            }

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

            this.parentMethod('resetAll', ['newKeys'])
        },

        // keys merger
        getUpdatedKey(item) {
            let mergerName = this.mergerName

            if (this.useReplace) {
                return item.replace(mergerName, '')
            }

            return mergerName ? `${mergerName}${item}` : item
        },
        mergeKeys() {
            this.toggleModal()

            let mergerName = this.mergerName

            if (this.keysList.includes(mergerName)) {
                this.$refs.search.value = mergerName
                this.searchFor = mergerName

                return this.showNotif(this.trans('merge_key_warning').replace(':attr', mergerName), 'danger')
            }

            // replace
            if (this.useReplace) {
                this.keysToBeMerged.map((old_key) => {
                    let new_key = old_key.replace(mergerName, '')

                    this.newKeyOps(old_key, new_key)
                })
            } else {
                // merge
                this.keysToBeMerged.map((old_key) => {
                    let new_key = `${mergerName}${old_key}`

                    this.newKeyOps(old_key, new_key)
                })
            }

            // this.mergerName = ''
            this.useReplace = false
            this.submitNewData()
            this.wrapAll()
        },
        wrapAll() {
            if (this.mergerKeysCount) {
                return this.keysToBeMerged = []
            }

            this.keysToBeMerged = this.keysList
        },
        toggleModal(val = false) {
            // save changed data first
            if (val && this.dataChanged) {
                return this.showNotif(this.trans('merge_warning'), 'warning')
            }

            this.showModal = val
        },

        // util
        arryFilter(arr) {
            return arr.filter((e) => e)
        },
        clickOnCkBox(id) {
            document.querySelector(`#${id}`).click()
        },
        newEntry() {
            this.dataChanged = true
        },
        newKeyOps(old_key, new_key, item = null) {
            if (old_key !== new_key) {
                this.newEntry()

                if (item) {
                    this.HLChanged(item)
                }

                if (this.newKeys) {
                    return this.newKeys[old_key] = new_key
                }

                this.newKeys = {[old_key]: new_key}
            }
        },
        saveNewKey(e) {
            let target = e.target

            let text = target.innerText = target.innerText.toLowerCase().replace(/\s/g, '_')
            let old_key = target.dataset.mainKey
            let new_key = text

            this.newKeyOps(old_key, new_key, target)
        },
        saveNewValue(e) {
            let target = e.target

            let code = target.dataset.code
            let key = target.dataset.mainKey
            let value = target.innerText = target.innerText.replace(/\n/g, '<br>')
            let fileData = this.selectedFileDataClone

            fileData.some((e, i) => {
                if (e.name == key && e.locales[code] !== value) {
                    this.newEntry()
                    this.HLChanged(target)
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
        getTTC(key, val) {
            let k = key

            // place holders
            let arr = this.arryFilter(Object.values(val))
            if (arr.length) {
                let test = arr[0].match(new RegExp(/:\w+/g))

                if (test) {
                    let str = ', ['
                    test.forEach((e) => {
                        str += `'${e.replace(':', '')}' => '', `
                    })
                    str += ']'
                    str = str.replace(', ]', '])')

                    k = k.replace(new RegExp(/[)$]/g), str)
                    return `<span style="cursor: pointer" class="c2c">${k}</span>`
                }
            }

            return `<span style="cursor: pointer" class="c2c">${k}</span>`
        },
        HLChanged(item) {
            item.classList.remove('nestedKeys')
            item.classList.add('changedKeys')
        },

        // events
        onClick(e) {
            let item = e.target

            if (item.classList.contains('c2c')) {
                this.$copyText(item.textContent)
            }
        },
        onKeydown(e) {
            let esc = e.keyCode == '27'

            if (esc) {
                if (this.showModal) {
                    this.showModal = false
                }

                if (this.isFocused('search', e)) {
                    this.resetSearch()
                }
            }
        },
        isFocused(item, e) {
            return this.$refs[item] && this.$refs[item].contains(e.target)
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
