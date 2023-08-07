<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\SignatureValidator;

use Illuminate\Http\Request;
use WayOfDev\WebhookClient\Exceptions\InvalidConfig;
use WayOfDev\WebhookClient\WebhookConfig;

use function hash_equals;
use function hash_hmac;

class DefaultSignatureValidator implements SignatureValidator
{
    /**
     * @throws InvalidConfig
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $signature = $request->header($config->signatureHeaderName);

        if (null === $signature || '' === $signature) {
            return false;
        }

        $signingSecret = $config->signingSecret;

        if ('' === $signingSecret) {
            throw InvalidConfig::signingSecretNotSet();
        }

        $computedSignature = hash_hmac('sha256', $request->getContent(), $signingSecret);

        return hash_equals($signature, $computedSignature);
    }
}
