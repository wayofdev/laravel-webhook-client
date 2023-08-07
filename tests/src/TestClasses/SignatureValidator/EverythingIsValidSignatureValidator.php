<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Tests\TestClasses\SignatureValidator;

use Illuminate\Http\Request;
use WayOfDev\WebhookClient\SignatureValidator\SignatureValidator;
use WayOfDev\WebhookClient\WebhookConfig;

class EverythingIsValidSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        return true;
    }
}
