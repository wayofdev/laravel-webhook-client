<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Exceptions;

use Exception;
use WayOfDev\WebhookClient\Bridge\Laravel\Jobs\ProcessWebhookJob;
use WayOfDev\WebhookClient\Contracts\RespondsToWebhook;
use WayOfDev\WebhookClient\Contracts\SignatureValidator;
use WayOfDev\WebhookClient\Contracts\WebhookProfile;

final class InvalidConfig extends Exception
{
    public static function couldNotFindConfig(string $notFoundConfigName): self
    {
        return new self("Could not find the configuration for `{$notFoundConfigName}`");
    }

    public static function invalidSignatureValidator(string $invalidSignatureValidator): self
    {
        $signatureValidatorInterface = SignatureValidator::class;

        return new self("`{$invalidSignatureValidator}` is not a valid signature validator class. A valid signature validator is a class that implements `{$signatureValidatorInterface}`.");
    }

    public static function invalidWebhookProfile(string $webhookProfile): self
    {
        $webhookProfileInterface = WebhookProfile::class;

        return new self("`{$webhookProfile}` is not a valid webhook profile class. A valid web hook profile is a class that implements `{$webhookProfileInterface}`.");
    }

    public static function invalidWebhookResponse(string $webhookResponse): self
    {
        $webhookResponseInterface = RespondsToWebhook::class;

        return new self("`{$webhookResponse}` is not a valid webhook response class. A valid webhook response is a class that implements `{$webhookResponseInterface}`.");
    }

    public static function invalidProcessWebhookJob(string $processWebhookJob): self
    {
        $abstractProcessWebhookJob = ProcessWebhookJob::class;

        return new self("`{$processWebhookJob}` is not a valid process webhook job class. A valid class should implement `{$abstractProcessWebhookJob}`.");
    }

    public static function signingSecretNotSet(): self
    {
        return new self('The webhook signing secret is not set. Make sure that the `signing_secret` config key is set to the correct value.');
    }

    public static function invalidPrunable(mixed $value): self
    {
        return new self("`{$value}` is not a valid amount of days.");
    }
}
