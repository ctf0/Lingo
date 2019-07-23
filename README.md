<h1 align="center">
    Lingo
    <br>
    <a href="https://packagist.org/packages/ctf0/lingo"><img src="https://img.shields.io/packagist/v/ctf0/lingo.svg" alt="Latest Stable Version" /></a> <a href="https://packagist.org/packages/ctf0/lingo"><img src="https://img.shields.io/packagist/dt/ctf0/lingo.svg" alt="Total Downloads" /></a>
</h1>

A file based translation manager, which unlike other Lang managers don't need a database connection to handle the translation.

<p align="center">
    <img src="https://user-images.githubusercontent.com/7388088/41813078-257e4b6c-772f-11e8-9661-14636218d029.png">
</p>

<br>

## Installation

- `composer require ctf0/lingo`

- (Laravel < 5.5) add the service provider

    ```php
    'providers' => [
        ctf0\Lingo\LingoServiceProvider::class,
    ]
    ```

- publish the package assets with

    `php artisan vendor:publish --provider="ctf0\Lingo\LingoServiceProvider"`

- after installation, run `php artisan lingo:setup` to add
    + package routes to `routes/web.php`
    + package assets compiling to `webpack.mix.js`

- install dependencies

    ```bash
    yarn add vue vue-ls vue-notif vue-clipboard2 vue-tippy@v1 vue-awesome@v2 axios fuse.js
    # or
    npm install vue vue-ls vue-notif vue-clipboard2 vue-tippy@v1 vue-awesome@v2 axios fuse.js --save
    ```

- add this one liner to your main js file and run `npm run watch` to compile your `js/css` files.
    - if you are having issues [Check](https://ctf0.wordpress.com/2017/09/12/laravel-mix-es6/)

    ```js
    // app.js

    window.Vue = require('vue')

    require('../vendor/Lingo/js/manager')

    new Vue({
        el: '#app'
    })
    ```

<br>

> ### Upgrading to v1.4.x<br>
> because code was refactored you'll need to republish the package files<br>
> `php artisan vendor:publish --provider="ctf0\Lingo\LingoServiceProvider" --force`

<br>

## Features

- filter by keys.
- sort table by keys or by values.
- add/remove "vendor/locale/file/item".
- show guiding steps while adding new vendor for better UX.
- validate for "vendor/locale/file" existence on the fly.
- use localeStorage to remember opened "tab/vendor/files/copy-format".
- support up to 3 levels deep on nested keys.
- support all laravel translation key formats.
- directly copy translation key along with placeholders through tool-tips<br>
  ex.`trans('Vendor::file.key', ['attr'=>''])`.
- copy/paste items from one file to another.
- merge/destruct multiple items.
- highlight changed key/value.
- download vendor/file.
- show/hide different elements to avoid noise & keep the user focused.
- shortcuts

    |             operation             | keyboard |
    |-----------------------------------|----------|
    | reset search ***"when focused"*** | esc      |
    | hide modal                        | esc      |

<br>

## Usage
> [Demo](https://github.com/ctf0/demos/tree/lingo)

- visit `localhost:8000/lingo`
