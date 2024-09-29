<?php

namespace App\Http;

use App\Http\Middleware\UpgradeToHttpsUnderNgrok;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // Other properties and methods...

    protected $routeMiddleware = [
        // Other middleware entries...
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        
    ];
    protected $middleware = [
        \App\Http\Middleware\Cors::class,
        // Other middleware...
    ];
    
    protected $middlewareGroups = [
        'web' => [
            UpgradeToHttpsUnderNgrok::class,
        ],
    ];   
}
