<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Bridge\Laravel\Providers;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use WayOfDev\WebhookClient\Bridge\Laravel\Http\Controllers\WebhookController;
use WayOfDev\WebhookClient\Contracts\WebhookCallRepository;
use WayOfDev\WebhookClient\Exceptions\InvalidConfig;
use WayOfDev\WebhookClient\Persistence\ORMWebhookCallRepository;
use WayOfDev\WebhookClient\WebhookConfig;
use WayOfDev\WebhookClient\WebhookConfigRepository;

use function is_null;

final class WebhookClientServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../../../config/webhook-client.php' => config_path('webhook-client.php'),
            ], 'config');

            $this->registerConsoleCommands();
        }
    }

    public function register(): void
    {
        Route::macro('webhooks', function (string $url, string $name = 'default') {
            return Route::post($url, WebhookController::class)->name("webhook-client-{$name}");
        });

        $this->app->scoped(WebhookConfigRepository::class, function () {
            $configRepository = new WebhookConfigRepository();

            collect(config('webhook-client.configs'))
                ->map(fn (array $config) => new WebhookConfig($config))
                ->each(fn (WebhookConfig $webhookConfig) => $configRepository->addConfig($webhookConfig));

            return $configRepository;
        });

        $this->app->bind(WebhookConfig::class, function () {
            $routeName = Route::currentRouteName() ?? '';

            $configName = Str::after($routeName, 'webhook-client-');

            $webhookConfig = app(WebhookConfigRepository::class)->getConfig($configName);

            if (is_null($webhookConfig)) {
                throw InvalidConfig::couldNotFindConfig($configName);
            }

            return $webhookConfig;
        });

        $this->registerWebhookCallRepository();
    }

    private function registerWebhookCallRepository(): void
    {
        $this->app->bind(
            WebhookCallRepository::class,
            ORMWebhookCallRepository::class
        );

        $this->app->when(ORMWebhookCallRepository::class)
            ->needs(Select::class)
            ->give(function (): Select {
                return new Select($this->app->make(ORMInterface::class), 'webhook_call');
            });
    }

    private function registerConsoleCommands(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../../../config/webhook-client.php', 'webhook-client');

        $this->commands([]);
    }
}
