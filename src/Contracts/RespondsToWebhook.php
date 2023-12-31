<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Contracts;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use WayOfDev\WebhookClient\Config;

interface RespondsToWebhook
{
    public function respondToValidWebhook(Request $request, Config $config): Response;
}
