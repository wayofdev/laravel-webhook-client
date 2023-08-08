<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Tests;

use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase as Orchestra;
use WayOfDev\Cycle\Bridge\Laravel\Providers\CycleServiceProvider;
use WayOfDev\Cycle\Testing\Concerns\InteractsWithDatabase;
use WayOfDev\Cycle\Testing\RefreshDatabase;
use WayOfDev\WebhookClient\Bridge\Laravel\Providers\WebhookClientServiceProvider;

use function array_merge;

abstract class TestCase extends Orchestra
{
    use InteractsWithDatabase;
    use RefreshDatabase;

    protected ?string $migrationsPath = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->migrationsPath = __DIR__ . '/../database/migrations/cycle';
        $this->cleanupMigrations($this->migrationsPath . '/*.php');
        $this->refreshDatabase();

        if (app()->environment() === 'testing') {
            config()->set([
                'cycle.tokenizer.directories' => array_merge(
                    config('cycle.tokenizer.directories'),
                    [__DIR__ . '/../../src/Entities'],
                    [__DIR__ . '/TestClasses/Entities'],
                ),
                'cycle.migrations.directory' => $this->migrationsPath,
            ]);
        }

        Artisan::call('cycle:migrate:init');
        Artisan::call('cycle:migrate', ['--force' => true]);
        Artisan::call('cycle:orm:migrate', ['--run' => true]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            CycleServiceProvider::class,
            WebhookClientServiceProvider::class,
        ];
    }
}
