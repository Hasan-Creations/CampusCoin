<?php

namespace App\Providers;

use App\Contracts\CategorizationProviderInterface;
use App\Services\AiCategorizationService;
use App\Services\Categorization\HeuristicCategorizationProvider;
use App\Services\Categorization\OpenAiCategorizationProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(HeuristicCategorizationProvider::class);

        $this->app->singleton(OpenAiCategorizationProvider::class, function ($app) {
            return new OpenAiCategorizationProvider(
                fallbackProvider: $app->make(HeuristicCategorizationProvider::class)
            );
        });

        $this->app->bind(CategorizationProviderInterface::class, function ($app) {
            return $app->make(OpenAiCategorizationProvider::class);
        });

        $this->app->singleton(AiCategorizationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
