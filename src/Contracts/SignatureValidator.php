<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Contracts;

use Illuminate\Http\Request;
use WayOfDev\WebhookClient\Config;

interface SignatureValidator
{
    public function isValid(Request $request, Config $config): bool;
}
