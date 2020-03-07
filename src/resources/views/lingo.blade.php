<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ trans('Lingo::messages.title') }}</title>

    {{-- Styles --}}
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bulma/0.7.1/css/bulma.min.css">
    <link rel="stylesheet" href="{{ asset('assets/vendor/Lingo/style.css') }}"/>
</head>

<body>
    <section id="app" v-cloak>

        {{-- notifications --}}
        <div class="notif-container">
            <my-notification></my-notification>
        </div>

        <div class="container">
            <div class="columns">
                {{-- lingo --}}
                <div class="column">

                    <lingo inline-template v-cloak
                        :translations="{{ $lingo_trans }}"
                        :routes={{ json_encode([
                            'addNewLocaleRoute' => route('lingo.add_new_locale'),
                            'addNewFileRoute' => route('lingo.add_new_file'),
                            'addNewVendorRoute' => route('lingo.add_new_vendor'),
                            'filesRoute' => route('lingo.get_files'),
                            'selectedFileDataRoute' => route('lingo.get_file_data'),
                            'deleteFileRoute' => route('lingo.delete_file'),
                            'deleteLocaleRoute' => route('lingo.delete_locale'),
                            'saveFileRoute' => route('lingo.save_file_data'),
                            'downloadVendorRoute' => route('lingo.download_dir'),
                            'downloadFileRoute' => route('lingo.download_file')
                        ]) }}>
                        <div>

                            {{-- add new vendor --}}
                            <transition name="lin-comp-fade">
                                <li class="columns" v-if="activeTabIs('vendor-tab')">
                                    <div class="column is-3">
                                        <p class="title is-marginless">
                                            {{-- steps --}}
                                            <span v-if="newVendor() && !selectedDirName" class="title has-text-success">1.</span>

                                            <span class="icon"><icon name="archive" scale="2"></icon></span>
                                            <span>{{ trans('Lingo::messages.new_vendor') }}</span>
                                        </p>
                                        <p class="subtitle is-marginless">@{{ vendorPH(new_vendor || 'Abc') }}</p>
                                    </div>

                                    <div class="column">
                                        <div class="field has-addons">
                                            <p class="control">
                                                <input class="input"
                                                    type="text"
                                                    @keyup.enter="addNewVendor()"
                                                    v-model="new_vendor"
                                                    placeholder="ex.'Abc'">
                                            </p>
                                            <p class="control">
                                                <button @click="addNewVendor()"
                                                    ref="vendor"
                                                    class="button is-primary"
                                                    :disabled="dirExist">
                                                    {{ trans('Lingo::messages.add_btn') }}
                                                </button>
                                            </p>
                                        </div>

                                        <transition name="lin-slide-fade">
                                            <p class="help is-danger" v-if="dirExist">{{ trans('Lingo::messages.already_exist') }}</p>
                                        </transition>
                                    </div>
                                </li>
                            </transition>

                            {{-- add new lang --}}
                            <transition name="lin-comp-fade">
                                <li class="columns" v-if="localesList.length || newVendor()">
                                    <div class="column is-3">
                                        <p class="title is-marginless">
                                            {{-- steps --}}
                                            <span v-if="newVendor() && localesList.length == 0" class="title has-text-success">2.</span>

                                            <span class="icon"><icon name="globe" scale="2"></icon></span>
                                            <span>{{ trans('Lingo::messages.new_locale') }}</span>
                                        </p>
                                        <p class="subtitle is-marginless">@{{ placeHolder(new_locale || 'fr') }}</p>
                                    </div>

                                    <div class="column">
                                        <div class="field has-addons">
                                            <p class="control">
                                                <input class="input"
                                                    type="text"
                                                    @keyup.enter="addNewLocale()"
                                                    v-model="new_locale"
                                                    placeholder="ex.'fr'">
                                            </p>
                                            <p class="control">
                                                <button @click="addNewLocale()"
                                                    ref="locale"
                                                    class="button is-primary"
                                                    :disabled="localeExist">
                                                    {{ trans('Lingo::messages.add_btn') }}
                                                </button>
                                            </p>
                                        </div>

                                        <transition name="lin-slide-fade">
                                            <p class="help is-danger" v-if="localeExist">{{ trans('Lingo::messages.already_exist') }}</p>
                                        </transition>
                                    </div>
                                </li>
                            </transition>

                            {{-- add new file --}}
                            <transition name="lin-comp-fade">
                                <li class="columns" v-if="filesList.length || newVendor()">
                                    <div class="column is-3">
                                        <p class="title is-marginless">
                                            {{-- steps --}}
                                            <span v-if="newVendor()" class="title has-text-success">3.</span>

                                            <span class="icon"><icon name="file-o" scale="2"></icon></span>
                                            <span>{{ trans('Lingo::messages.new_file') }}</span>
                                        </p>
                                        <p class="subtitle is-marginless">@{{ placeHolder(new_file || 'messages.php', '*/') }}</p>
                                    </div>

                                    <div class="column">
                                        <div class="field has-addons">
                                            <p class="control">
                                                <input class="input"
                                                    type="text"
                                                    @keyup.enter="addNewFile()"
                                                    v-model="new_file"
                                                    placeholder="ex.'messages.php'">
                                            </p>
                                            <p class="control">
                                                <button @click="addNewFile()"
                                                    ref="file"
                                                    class="button is-primary"
                                                    :disabled="fileExist">
                                                    {{ trans('Lingo::messages.add_btn') }}
                                                </button>
                                            </p>
                                        </div>

                                        <transition name="lin-slide-fade">
                                            <p class="help is-danger" v-if="fileExist">{{ trans('Lingo::messages.already_exist') }}</p>
                                        </transition>
                                    </div>
                                </li>
                            </transition>

                            {{-- utils --}}
                            <div class="level m-t-10">
                                <div class="level-right"></div>
                                <div class="level-left">
                                    {{-- copied key format --}}
                                    <div class="level-item">
                                        <div class="field">
                                            <div class="control has-icons-left">
                                                <div class="select">
                                                    <select v-model="selectedKeyFormat">
                                                        <option value="" disabled><span>{{ trans('Lingo::messages.key_format') }}</span></option>
                                                        <option value="clear">Non</option>
                                                        <option v-for="(item, index) in copyKeyFormat" :key="index">@{{ item }}</option>
                                                    </select>
                                                </div>
                                                <div class="icon is-medium is-left has-text-black-ter">
                                                    <icon name="keyboard-o"></icon>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabs --}}
                            <div class="tabs is-medium is-centered">
                                <ul>
                                    <li :class="{'is-active' : activeTabIs('default-tab')}">
                                        <a @click="toggleTab('default-tab')">
                                            <span class="icon"><icon name="files-o" scale="5"></icon></span>
                                            <span>{{ trans('Lingo::messages.d_tab') }}</span>
                                        </a>
                                    </li>

                                    <li :class="{'is-active' : activeTabIs('vendor-tab')}">
                                        <a @click="toggleTab('vendor-tab')">
                                            <span class="icon"><icon name="archive" scale="5"></icon></span>
                                            <span>{{ trans('Lingo::messages.v_tab') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            {{-- Tabs Toggle --}}
                            <transition name="lin-comp-fade" mode="out-in">
                                <keep-alive>
                                    <component :is="activeTab"
                                        dirs-route="{{ route('lingo.vendor_dirs') }}"
                                        delete-vendor-route="{{ route('lingo.delete_vendor') }}">
                                    </component>
                                </keep-alive>
                            </transition>

                        </div>
                    </lingo>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    {{-- Scripts --}}
    <script src="{{ asset("js/app.js") }}"></script>
</body>
</html>
