# Lingo

[![Latest Stable Version](https://img.shields.io/packagist/v/ctf0/lingo.svg?style=for-the-badge)](https://packagist.org/packages/ctf0/lingo) [![Total Downloads](https://img.shields.io/packagist/dt/ctf0/lingo.svg?style=for-the-badge)](https://packagist.org/packages/ctf0/lingo)

A file based translation manager, which unlike other Lang managers don't need a database connection to handle the translation.

<p align="center">
    <img src="https://user-images.githubusercontent.com/7388088/32700479-b5c32ff0-c7ce-11e7-85ca-4895e802c68a.png">
</p>

<br>

## Installation

- package requires Laravel v5.4+

- `composer require ctf0/lingo`

- (Laravel < 5.5) add the service provider

```php
'providers' => [
    ctf0\Lingo\LingoServiceProvider::class,
]
```

- publish the package assets with

`php artisan vendor:publish --provider="ctf0\Lingo\LingoServiceProvider"`

- after installation, package will auto-add
    + package routes to `routes/web.php`
    + package assets compiling to `webpack.mix.js`
    + `MIX_LINGO_FRAMEWORK=bulma` to `.env`

- install dependencies

```bash
yarn add vue axios vue-ls vuemit vue-notif vue-clipboard2 vue-tippy@v1 vue-awesome
# or
npm install vue axios vue-ls vuemit vue-notif vue-clipboard2 vue-tippy@v1 vue-awesome --save
```

- add this one liner to your main js file and run `npm run watch` to compile your `js/css` files.
    - if you are having issues [Check](https://ctf0.wordpress.com/2017/09/12/laravel-mix-es6/)

```js
require('./../vendor/Lingo/js/manager')

new Vue({
    el: '#app'
})
```

<br>

## Features

- add/remove "vendor/locale/file/item".
- show guiding steps while adding new vendor for better UX.
- validate for "vendor/locale/file" existence on the fly.
- use localeStorage to remember opened "tab/vendor/files".
- show/hide different elements to avoid noise & keep the user focused.
- support up to 3 levels deep on nested keys.
- support all laravel different translation key formats.
- directly copy translation key by clicking on the tool-tip.

<br>

## Usage

- for styling we use ***bulma***

> ***Or Use another Framework***
>
> - duplicate `resources/views/vendor/Lingo/lingo-bulma` and rename it to the framework you want ex.`lingo-bootstrap`
> - duplicate `assets/vendor/Lingo/sass/bulma.scss` and rename it to the framework you want ex.`bootstrap.scss`
> - edit both
>     + `assets/vendor/Lingo/js/Tabs/vendor.vue`
>     + `assets/vendor/Lingo/js/Tabs/shared/content.vue`
> - set `MIX_LINGO_FRAMEWORK` to the framework name ex.`MIX_LINGO_FRAMEWORK=bootstrap`
> - start editing the new files.

- visit `localhost:8000/lingo`
