<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Tests\TestClasses\Jobs;

use WayOfDev\WebhookClient\Bridge\Laravel\Jobs\ProcessWebhookJob;

class ProcessWebhookJobTestClass extends ProcessWebhookJob
{
    public function handle(): void
    {
    }
}
