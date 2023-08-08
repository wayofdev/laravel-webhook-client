<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Tests\TestClasses\Responses;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use WayOfDev\WebhookClient\Config;
use WayOfDev\WebhookClient\Contracts\RespondsToWebhook;

class CustomRespondsToWebhook implements RespondsToWebhook
{
    public function respondToValidWebhook(Request $request, Config $config): Response
    {
        return response()->json(['foo' => 'bar']);
    }
}
