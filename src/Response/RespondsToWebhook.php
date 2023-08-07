<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Response;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use WayOfDev\WebhookClient\WebhookConfig;

interface RespondsToWebhook
{
    public function respondToValidWebhook(Request $request, WebhookConfig $config): Response;
}
