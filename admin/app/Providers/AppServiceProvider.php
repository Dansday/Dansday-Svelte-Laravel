<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Use public_html when present (e.g. shared hosting after renaming public → public_html)
        if (is_dir($this->app->basePath('public_html'))) {
            $this->app->bind('path.public', fn () => $this->app->basePath('public_html'));
        }
    }

    public function boot(): void
    {
        Model::automaticallyEagerLoadRelationships();

        Schema::defaultStringLength(191);
    }
}
