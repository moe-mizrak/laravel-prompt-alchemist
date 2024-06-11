<?php

namespace MoeMizrak\LaravelPromptAlchemist;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use MoeMizrak\LaravelOpenrouter\OpenRouterServiceProvider;
use MoeMizrak\LaravelPromptAlchemist\Facades\LaravelPromptAlchemist;

class PromptAlchemistServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPublishing();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->configure();

        // Setup open router service provider and configuration.
        $this->configureOpenRouter();

        $this->app->bind('laravel-prompt-alchemist', function () {
            return new PromptAlchemistRequest();
        });

        $this->app->bind(PromptAlchemistRequest::class, function () {
            return $this->app->make('laravel-prompt-alchemist');
        });

        // Register the facade alias.
        AliasLoader::getInstance()->alias('LaravelPromptAlchemist', LaravelPromptAlchemist::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['laravel-prompt-alchemist'];
    }

    /**
     * Setup the configuration.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-prompt-alchemist.php', 'laravel-prompt-alchemist'
        );
    }

    /**
     * Register the OpenRouter service provider and setup configuration values.
     *
     * @return void
     */
    protected function configureOpenRouter(): void
    {
        // Register OpenRouterServiceProvider
        $this->app->register(OpenRouterServiceProvider::class);

        // Set environment variables if not already set from laravel-prompt-alchemist config file
        if (! env('OPENROUTER_API_KEY') || ! env('OPENROUTER_API_ENDPOINT')) {
            config(['laravel-openrouter.api_key' => config('laravel-prompt-alchemist.env_variables.api_key')]);
            config(['laravel-openrouter.api_endpoint' => config('laravel-prompt-alchemist.env_variables.api_endpoint')]);
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-prompt-alchemist.php' => config_path('laravel-prompt-alchemist.php'),
            ], 'laravel-prompt-alchemist');
        }
    }
}