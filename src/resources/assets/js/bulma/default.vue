<template>
    <div>
        <!-- select file -->
        <div class="field is-grouped is-grouped-right" v-if="files.length">
            <div class="control has-icons-left">
                <div class="select">
                    <select v-model="selectedFile">
                        <option value="" disabled>{{ $parent.lingoTrans.select_file[$parent.currentLocale] || '' }}</option>
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
                    <p>{{ $parent.lingoTrans.warn[$parent.currentLocale] || '' }}</p>
                    <button class="delete" aria-label="delete" @click="hasNesting = false"></button>
                </div>
                <div class="message-body">{{ $parent.lingoTrans.warn_msg[$parent.currentLocale] || '' }}</div>
            </article>

            <!-- data -->
            <table class="table is-fullwidth is-bordered">
                <thead>
                    <tr class="is-unselectable">
                        <th width="1%">{{ $parent.lingoTrans.key[$parent.currentLocale] || '' }}</th>
                        <th v-for="(l, i) in locales" :key="i">
                            <div class="tags has-addons">
                                <span class="tag is-dark is-medium">{{ l }}</span>
                                <span class="tag is-warning is-medium" @click="removeLocale(l)">
                                    <i class="fa fa-trash"></i>
                                </span>
                            </div>
                        </th>
                        <th>{{ $parent.lingoTrans.ops[$parent.currentLocale] || '' }}</th>
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
                            {{ $parent.lingoTrans.no_data[$parent.currentLocale] || '' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- ops -->
            <div class="level">
                <div class="level-right">
                    <div class="level-item">
                        <button class="button is-info" @click.prevent="addNewItem()">
                            {{ $parent.lingoTrans.add_new[$parent.currentLocale] || '' }}
                        </button>
                    </div>
                </div>
                <div class="level-left">
                    <div class="level-item">
                        <button class="button is-success" :disabled="!dataChanged" @click="submitNewData()">
                            {{ $parent.lingoTrans.save[$parent.currentLocale] || '' }}
                        </button>
                    </div>
                    <div class="level-item">
                        <button class="button" :disabled="!dataChanged" @click="resetData()">
                            {{ $parent.lingoTrans.reset[$parent.currentLocale] || '' }}
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
import Ops from './../mixins/taps-ops'

export default {
    name: 'default-tab',
    mixins: [Ops],
    mounted() {
        this.getFiles()
    },
    methods: {
        getFiles() {
            $.post(this.routes.filesRoute, {}, (data) => {
                if (data.success) {
                    this.files = data.message
                }

            }).fail(() => {
                this.$parent.failedAjax()
            })
        }
    }
}
</script>
