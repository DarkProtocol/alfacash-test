<?php

declare(strict_types=1);

namespace App\Services\Exchange\Providers;

use Illuminate\Routing\Router;
use Lucid\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function map(Router $router): void
    {
        $namespace = 'App\Services\Exchange\Http\Controllers';
        $pathApi = __DIR__ . '/../routes/routes.php';
        $this->mapApiRoutes($router, $namespace, $pathApi, '');
    }
}
