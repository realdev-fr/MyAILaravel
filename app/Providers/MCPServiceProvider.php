<?php

namespace App\Providers;

use App\Services\MCPService;
use App\Services\SimpleMCPService;
use App\Contracts\MCPServiceInterface;
use Illuminate\Support\ServiceProvider;

class MCPServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Utiliser SimpleMCPService temporairement pour éviter les problèmes ReactPHP
        $this->app->singleton(MCPServiceInterface::class, function ($app) {
            return new SimpleMCPService();
        });

        // Keep the concrete class binding for compatibility
        $this->app->singleton(MCPService::class, function ($app) {
            return new SimpleMCPService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}