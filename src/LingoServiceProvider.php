<?php

namespace ctf0\Lingo;

use Illuminate\Support\ServiceProvider;

class LingoServiceProvider extends ServiceProvider
{
    protected $file;

    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->file = app('files');

        $this->packagePublish();

        // append extra data
        if (!app('cache')->store('file')->has('ct-lingo')) {
            $this->autoReg();
        }
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
            $current   = app()->getLocale();
            $fall_back = config('app.fallback_locale');
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
     * [autoReg description].
     *
     * @return [type] [description]
     */
    protected function autoReg()
    {
        // routes
        $route_file = base_path('routes/web.php');
        $search     = 'Lingo';

        if ($this->checkExist($route_file, $search)) {
            $data = "\n// Lingo\nctf0\Lingo\LingoRoutes::routes();";

            $this->file->append($route_file, $data);
        }

        // mix
        $mix_file = base_path('webpack.mix.js');
        $search   = 'Lingo';

        if ($this->checkExist($mix_file, $search)) {
            $data = "\n// Lingo\nrequire('dotenv').config()\nmix.sass('resources/assets/vendor/Lingo/style.scss', 'public/assets/vendor/Lingo/style.css').version();";

            $this->file->append($mix_file, $data);
        }

        // run check once
        app('cache')->store('file')->rememberForever('ct-lingo', function () {
            return 'added';
        });
    }

    /**
     * [checkExist description].
     *
     * @param [type] $file   [description]
     * @param [type] $search [description]
     *
     * @return [type] [description]
     */
    protected function checkExist($file, $search)
    {
        return $this->file->exists($file) && !str_contains($this->file->get($file), $search);
    }

    /**
     * Register any package services.
     */
    public function register()
    {
        $this->app->register(\Themsaid\Langman\LangmanServiceProvider::class);
        $this->app->register(\ctf0\PackageChangeLog\PackageChangeLogServiceProvider::class);
    }
}
