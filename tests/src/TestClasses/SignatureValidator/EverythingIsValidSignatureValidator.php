<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Tests\TestClasses\SignatureValidator;

use Illuminate\Http\Request;
use WayOfDev\WebhookClient\Config;
use WayOfDev\WebhookClient\Contracts\SignatureValidator;

class EverythingIsValidSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, Config $config): bool
    {
        return true;
    }
}
