<p align="center">
    <img src="https://user-images.githubusercontent.com/7388088/31319438-bcfac926-ac63-11e7-8acc-60ac45aa465b.png">
</p>

# Lingo

is a file based translation manager, which unlike other Lang managers dont need a database connection to handle the translation.

## Installation

- package requires Laravel v5.4+

- `composer require ctf0/lingo`

- (Laravel < 5.5) add the service provider

```php
'providers' => [
    ctf0\Lingo\LingoServiceProvider::class,
]
```

- install dependencies

```bash
yarn add vue vuemit vue-notif vue-clipboard2 vue-tippy
# or
npm install vue vuemit vue-notif vue-clipboard2 vue-tippy --save
```

- publish the package assets with

`php artisan vendor:publish --provider="ctf0\Lingo\LingoServiceProvider"`

- after installation, package will auto-add
    + package routes to `routes/web.php`
    + package assets compiling to `webpack.mix.js`
    + `MIX_LINGO_FRAMEWORK=bulma` to `.env`

## Features

- add/remove "vendor/locale/file/item".
- show guiding steps while adding new vendor for better UX.
- validate for "vendor/locale/file" existence on the fly.
- use localeStorage to remember opened "tab/vendor/files".
- show/hide different elements to avoid noise & keep the user focused.
- support up to 3 levels deep on nested keys.
- copy translation key by clicking on it.

## Usage

- for styling we use ***bulma***

> ***Or Use another Framework***
>
> - duplicate `resources/views/vendor/Lingo/lingo-bulma` and rename it to the framework you want ex.`lingo-bootstrap`
> - duplicate `assets/vendor/Lingo/sass/bulma.scss` and rename it to the framework you want ex.`bootstrap.scss`
> - edit `assets/vendor/Lingo/js/shared/content.vue` & `assets/vendor/Lingo/js/Tabs/vendor.vue`
> - set `MIX_LINGO_FRAMEWORK` to the framework name ex.`MIX_LINGO_FRAMEWORK=bootstrap`
> - start editing the new files.

- add this one liner to your main js file and run `npm run watch` to compile your `js/css` files.
    + if you are having issues [Check](https://ctf0.wordpress.com/2017/09/12/laravel-mix-es6/)

```js
require('./../vendor/Lingo/js/lingo')

new Vue({
    el: '#app'
})
```

- now visit `localhost:8000/lingo`

## Notes

- we use [Laravel Langman](https://github.com/themsaid/laravel-langman) for scanning for missing translation keys, if you have better alternative you can open a ticket.
