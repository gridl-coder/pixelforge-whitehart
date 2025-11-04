<?php

namespace PixelForge\Providers;

use Roots\Acorn\Sage\SageServiceProvider;

class ThemeServiceProvider extends SageServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->alias('sage.view', 'pixelforge.view');
        $this->app->alias('sage.data', 'pixelforge.data');
        $this->app->alias('sage.blade', 'pixelforge.blade');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
