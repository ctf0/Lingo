<?php

namespace ctf0\Lingo;

use Illuminate\Support\Arr;
use ctf0\Lingo\Commands\PackageSetup;
use Illuminate\Support\ServiceProvider;

class LingoServiceProvider extends ServiceProvider
{
    protected $file;

    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->file = $this->app['files'];

        $this->packagePublish();
        $this->registerMacro();
        $this->command();
    }

    protected function registerMacro()
    {
        $this->app['router']->macro('setGroupNamespace', function ($namesapce = null) {
            $lastGroupStack = array_pop($this->groupStack);

            if ($lastGroupStack !== null) {
                Arr::set($lastGroupStack, 'namespace', $namesapce);
                $this->groupStack[] = $lastGroupStack;
            }

            return $this;
        });
    }

    /**
     * [packagePublish description].
     *
     * @return [type] [description]
     */
    public function packagePublish()
    {
        // resources
        $this->publishes([
            __DIR__ . '/resources/assets' => resource_path('assets/vendor/Lingo'),
        ], 'assets');

        // views
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'Lingo');
        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/Lingo'),
        ], 'views');

        // trans
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'Lingo');
        $this->publishes([
            __DIR__ . '/resources/lang' => resource_path('lang/vendor/Lingo'),
        ], 'trans');

        $this->viewComp();
    }

    protected function viewComp()
    {
        $path = resource_path('lang/vendor/Lingo');

        if ($this->file->exists($path)) {
            $current   = $this->app->getLocale();
            $fall_back = $this->app['config']->get('app.fallback_locale');
            $file_name = 'messages.php';

            $trans = file_exists("$path/$current/$file_name")
               ? include "$path/$current/$file_name"
               : include "$path/$fall_back/$file_name";

            return view()->composer('Lingo::*', function ($view) use ($trans) {
                $view->with(['lingo_trans' => json_encode($trans)]);
            });
        }

        view()->composer('Lingo::*', function ($view) {
            $view->with(['lingo_trans' => json_encode([])]);
        });
    }

    /**
     * package commands.
     *
     * @return [type] [description]
     */
    protected function command()
    {
        $this->commands([
            PackageSetup::class,
        ]);
    }

    /**
     * Register any package services.
     */
    public function register()
    {
        $this->app->register(\ctf0\PackageChangeLog\PackageChangeLogServiceProvider::class);
    }
}
