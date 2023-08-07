<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\SignatureValidator;

use Illuminate\Http\Request;
use WayOfDev\WebhookClient\WebhookConfig;

interface SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool;
}
