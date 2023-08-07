<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Bridge\Laravel\Http\Controllers;

use Illuminate\Http\Request;
use WayOfDev\WebhookClient\Contracts\WebhookCallRepository;
use WayOfDev\WebhookClient\Exceptions\InvalidWebhookSignature;
use WayOfDev\WebhookClient\WebhookConfig;
use WayOfDev\WebhookClient\WebhookProcessor;

class WebhookController
{
    /**
     * @throws InvalidWebhookSignature
     */
    public function __invoke(Request $request, WebhookConfig $config, WebhookCallRepository $repository)
    {
        return (new WebhookProcessor($request, $config, $repository))->process();
    }
}
