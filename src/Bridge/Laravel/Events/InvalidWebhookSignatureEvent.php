<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Bridge\Laravel\Events;

use Illuminate\Http\Request;

class InvalidWebhookSignatureEvent
{
    public function __construct(
        public Request $request
    ) {
    }
}
