<?php

namespace Yajra\SQLLoader;

use Illuminate\Support\ServiceProvider;

class SQLLoaderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/sql-loader.php', 'sql-loader');
    }
}
