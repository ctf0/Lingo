<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lingo</title>

    {{-- Styles --}}
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bulma/0.6.0/css/bulma.min.css">
    <link rel="stylesheet" href="{{ asset('assets/vendor/Lingo/style.css') }}"/>
</head>

<body>
    <section id="app" v-cloak>

        {{-- notifications --}}
        <div class="notif-container">
            <my-notification></my-notification>
        </div>

        <div class="container is-fluid is-marginless">
            <div class="columns">
                {{-- lingo --}}
                <div class="column">

                    <lingo inline-template
                        current-locale="{{ app()->getLocale() }}"
                        scan-for-missing-route="{{ route('lingo.scan_for_missing') }}"
                        add-new-locale-route="{{ route('lingo.add_new_locale') }}"
                        add-new-file-route="{{ route('lingo.add_new_file') }}"
                        add-new-vendor-route="{{ route('lingo.add_new_vendor') }}"
                        files-route="{{ route('lingo.get_files') }}"
                        selected-file-data-route="{{ route('lingo.get_file_data') }}"
                        delete-file-route="{{ route('lingo.delete_file') }}"
                        delete-locale-route="{{ route('lingo.delete_locale') }}"
                        save-file-route="{{ route('lingo.save_file_data') }}">
                        <div>

                            {{-- scan for missing trans --}}
                            <div class="columns">
                                <div class="column is-2">
                                    <h3 class="title is-4">
                                        <span class="icon"><i class="fa fa-qrcode"></i></span>
                                        <span>{{ trans('Lingo::messages.scan') }}</span>
                                    </h3>
                                </div>
                                <div class="column">
                                    <button @click="scanForMissing()" class="button is-link">{{ trans('Lingo::messages.scan_btn') }}</button>
                                </div>
                            </div>

                            {{-- add new vendor --}}
                            <div class="columns" v-if="activeTabIs('vendor-tab')">
                                <div class="column is-2">
                                    <p class="title">
                                        {{-- steps --}}
                                        <transition name="slide-fade">
                                            <span v-if="newVendor() && !selectedDirName" class="title has-text-success">1.</span>
                                        </transition>

                                        <span class="icon"><i class="fa fa-archive"></i></span>
                                        <span>{{ trans('Lingo::messages.new_vendor') }}</span>
                                    </p>
                                    <p class="subtitle">@{{ vendorPH(new_vendor || 'Abc') }}</p>
                                </div>

                                <div class="column">
                                    <div class="field has-addons">
                                        <p class="control">
                                            <input class="input" type="text" @keyup.enter="addNewVendor()" v-model="new_vendor" placeholder="ex.'Abc'">
                                        </p>
                                        <p class="control">
                                            <button @click="addNewVendor()" ref="vendor" class="button is-primary" :disabled="dirExist">{{ trans('Lingo::messages.add_btn') }}</button>
                                        </p>
                                    </div>

                                    <transition name="slide-fade">
                                        <p class="help is-danger" v-if="dirExist">{{ trans('Lingo::messages.already_exist') }}</p>
                                    </transition>
                                </div>
                            </div>

                            {{-- add new lang --}}
                            <div class="columns" v-if="localesList.length || newVendor()">
                                <div class="column is-2">
                                    <p class="title">
                                        {{-- steps --}}
                                        <transition name="slide-fade">
                                            <span v-if="newVendor() && localesList.length == 0" class="title has-text-success">2.</span>
                                        </transition>

                                        <span class="icon"><i class="fa fa-globe"></i></span>
                                        <span>{{ trans('Lingo::messages.new_locale') }}</span>
                                    </p>
                                    <p class="subtitle">@{{ placeHolder(new_locale || 'fr') }}</p>
                                </div>

                                <div class="column">
                                    <div class="field has-addons">
                                        <p class="control">
                                            <input class="input" type="text" @keyup.enter="addNewLocale()" v-model="new_locale" placeholder="ex.'fr'">
                                        </p>
                                        <p class="control">
                                            <button @click="addNewLocale()" ref="locale" class="button is-primary" :disabled="localeExist">{{ trans('Lingo::messages.add_btn') }}</button>
                                        </p>
                                    </div>

                                    <transition name="slide-fade">
                                        <p class="help is-danger" v-if="localeExist">{{ trans('Lingo::messages.already_exist') }}</p>
                                    </transition>
                                </div>
                            </div>

                            {{-- add new file --}}
                            <div class="columns" v-if="filesList.length || newVendor()">
                                <div class="column is-2">
                                    <p class="title">
                                        {{-- steps --}}
                                        <transition name="slide-fade">
                                            <span v-if="newVendor()" class="title has-text-success">3.</span>
                                        </transition>

                                        <span class="icon"><i class="fa fa-file-o"></i></span>
                                        <span>{{ trans('Lingo::messages.new_file') }}</span>
                                    </p>
                                    <p class="subtitle">@{{ placeHolder(new_file || 'messages.php', '*/') }}</p>
                                </div>

                                <div class="column">
                                    <div class="field has-addons">
                                        <p class="control">
                                            <input class="input" type="text" @keyup.enter="addNewFile()" v-model="new_file" placeholder="ex.'messages.php'">
                                        </p>
                                        <p class="control">
                                            <button @click="addNewFile()" ref="file" class="button is-primary" :disabled="fileExist">{{ trans('Lingo::messages.add_btn') }}</button>
                                        </p>
                                    </div>

                                    <transition name="slide-fade">
                                        <p class="help is-danger" v-if="fileExist">{{ trans('Lingo::messages.already_exist') }}</p>
                                    </transition>
                                </div>
                            </div>

                            {{-- Tabs --}}
                            <div class="tabs is-medium is-centered m-t-50">
                                <ul>
                                    <li :class="{'is-active' : activeTabIs('default-tab')}">
                                        <a @click="toggleTab('default-tab')">
                                            <span class="icon is-small"><i class="fa fa-files-o"></i></span>
                                            <span>{{ trans('Lingo::messages.d_tab') }}</span>
                                        </a>
                                    </li>

                                    <li :class="{'is-active' : activeTabIs('vendor-tab')}">
                                        <a @click="toggleTab('vendor-tab')">
                                            <span class="icon is-small"><i class="fa fa-archive"></i></span>
                                            <span>{{ trans('Lingo::messages.v_tab') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            {{-- Tabs Toggle --}}
                            <keep-alive>
                                <component :is="activeTab"
                                    dirs-route="{{ route('lingo.vendor_dirs') }}"
                                    delete-vendor-route="{{ route('lingo.delete_vendor') }}">
                                </component>
                            </keep-alive>

                        </div>
                    </lingo>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    {{-- Scripts --}}
    <script src="//code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/floatthead/2.0.3/jquery.floatThead.min.js"></script>
    <script src="{{ asset("path/to/app.js") }}"></script>
</body>
</html>
