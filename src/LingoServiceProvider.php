<?php

namespace ctf0\Lingo;

use ctf0\PackageChangeLog\PackageChangeLogServiceProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Themsaid\Langman\LangmanServiceProvider;

class LingoServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {

        $this->registerRoutes();
        $this->registerResources();
        $this->defineAssetPublishing();

    }

    protected function registerRoutes()
    {
        Route::group([
            'prefix'     => config('lingo.uri', 'lingo'),
            'namespace'  => 'ctf0\Lingo\Http\Controllers',
            'middleware' => config('lingo.middleware', 'web'),
            'as'         => config('lingo.as', 'lingo.'),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    protected function registerResources()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Lingo');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Lingo');


        $locale = app()->getLocale();
        $fall_back = config('app.fallback_locale');
        $file_name = 'messages.php';

        $path = LINGO_PATH . "/resources/lang/{$locale}/$file_name";
        $path_fallback = LINGO_PATH . "/resources/lang/{$fall_back}/$file_name";

        $trans = file_exists($path)
            ? include "$path"
            : include "$path_fallback";

        if (!app('cache')->store('file')->has('ct-lingo')) {
            view()->composer('Lingo::*', function (View $view) use ($trans) {
                $view->with([
                    'lingo_trans' => json_encode($trans),
                ]);
            });
        }

    }

    protected function defineAssetPublishing()
    {
        $this->publishes([
            LINGO_PATH . '/public' => public_path('vendor/lingo'),
        ], 'lingo-assets');
    }

    public function register()
    {
        if (!defined('LINGO_PATH')) {
            define('LINGO_PATH', realpath(__DIR__ . '/../'));
        }

        $this->configure();
        $this->offerPublishing();
        $this->registerServices();

    }

    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/lingo.php', 'Lingo'
        );
    }

    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/lingo.php' => config_path('lingo.php'),
            ], 'lingo-config');
        }
    }

    protected function registerServices()
    {
        $this->app->register(LangmanServiceProvider::class);
        $this->app->register(PackageChangeLogServiceProvider::class);
    }
}
