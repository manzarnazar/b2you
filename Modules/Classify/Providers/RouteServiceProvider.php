<?php

namespace Modules\Classify\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\Classify\Http\Controllers';

    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->prefix('admin')
            ->as('admin.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Classify', '/Routes/web/admin/admin.php'));

        Route::middleware('web')
            ->prefix('vendor-panel')
            ->as('vendor.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Classify', '/Routes/web/vendor/routes.php'));
    }

    protected function mapApiRoutes()
    {
        Route::prefix('api/v1')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Classify', '/Routes/api/v1/api.php'));
    }
}
