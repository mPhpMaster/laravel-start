<?php
/*
 * Copyright © 2021. mPhpMaster(https://github.com/mPhpMaster) All rights reserved.
 */

namespace mPhpMaster\LaravelStart\Providers;

use Illuminate\Database\Schema\Builder;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Class HelpersProvider
 *
 * @package mPhpMaster\LaravelStart\Providers
 */
class HelpersProvider extends ServiceProvider
{
    const MIXINS_DIR = __DIR__ . '/../Mixins';

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerMixins();
    }

    /**
     * Bootstrap services.
     *
     * @param Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        Builder::defaultStringLength(191);
        Schema::defaultStringLength(191);

        $this->loadViewsFrom(__DIR__.'/Views', 'laravel-start');

        if ($this->app->runningInConsole())
        {
            $this->publishes([
                __DIR__ . '/../Views/js_routes.blade.php' =>
                    $this->app->resourcePath('views/vendor/laravel-start/js_routes.blade.php'),
            ], 'js_routes');
        }
        

        /**
         * Helpers
         */
        require_once __DIR__ . '/../Helpers/Functions.php';
        require_once __DIR__ . '/../Helpers/Files.functions.php';

        /**
         * @internal example for {@link createNewValidator}
         */
        createNewValidator(
            "mobile_sa",
            function ($attribute, $value, $parameters, $validator) {
                $value = trim($value, ' +.');
                $value = ltrim($value, '0');
                $value = ltrim($value, '966');
                return \Illuminate\Support\Str::startsWith("0{$value}", "05") && strlen($value) === 9;
            });
        $this->map();
    }


    /**
     * Register Mixins
     */
    public function registerMixins()
    {
        if ( !function_exists('cutBasePath') ) {
            $cutBasePath = static function ($fullFilePath = null, $prefix = '') {
                $fullFilePath = $fullFilePath ?:
                    Arr::get(@current(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)), 'file', null);

                return $prefix . str_ireplace(base_path() . DIRECTORY_SEPARATOR, '', $fullFilePath ?: __FILE__);
            };
        } else {
            $cutBasePath = 'cutBasePath';
        }

        Collection::make(
//            glob(real_path($cutBasePath(self::MIXINS_DIR__ . "/*Invoke.php")))
            glob(fixPath(real_path($cutBasePath("/*Invoke.php", self::MIXINS_DIR))))
        )
            ->mapWithKeys(static function ($path) {
                $file_name = pathinfo($path, PATHINFO_FILENAME);
                return [
                    "mPhpMaster\\LaravelStart\\Mixins\\" . $file_name => Str::replaceLast('Invoke', '', $file_name),
                ];
            })
            ->reject(static function ($macro) {
                return Collection::hasMacro($macro);
            })
            ->each(static function ($macro, $path) use ($cutBasePath) {
                $class = str_ireplace('/', DIRECTORY_SEPARATOR, $cutBasePath($path));

                Collection::macro(Str::camel($macro), app($class)());
            });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapJSRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapJSRoutes()
    {
        Route::prefix('JS')->as('js::')->group(function () {
            Route::get('routes_script', "\\mPhpMaster\\LaravelStart\\Controllers\\JSRoutes@routes")->name('routes');
            Route::get('routes/{any?}', "\\mPhpMaster\\LaravelStart\\Controllers\\JSRoutes@print_routes")->name('print_routes');
        });
// alias for line: 137
        Route::get('routes/{any?}', "\\mPhpMaster\\LaravelStart\\Controllers\\JSRoutes@print_routes")->name('print_routes_no_ns');
    }
    
    /**
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
