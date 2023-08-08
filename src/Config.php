<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient;

use WayOfDev\WebhookClient\Bridge\Laravel\Jobs\ProcessWebhookJob;
use WayOfDev\WebhookClient\Contracts\RespondsToWebhook;
use WayOfDev\WebhookClient\Contracts\SignatureValidator;
use WayOfDev\WebhookClient\Contracts\WebhookProfile;
use WayOfDev\WebhookClient\Exceptions\InvalidConfig;
use WayOfDev\WebhookClient\Response\DefaultRespondsTo;

use function is_subclass_of;

class Config
{
    public string $name;

    public string $signingSecret;

    public string $signatureHeaderName;

    public SignatureValidator $signatureValidator;

    public WebhookProfile $webhookProfile;

    public RespondsToWebhook $webhookResponse;

    public string $webhookEntity;

    public array|string $storeHeaders;

    public string $processWebhookJobClass;

    /**
     * @throws InvalidConfig
     */
    public function __construct(array $properties)
    {
        $this->name = $properties['name'];

        $this->signingSecret = $properties['signing_secret'] ?? '';

        $this->signatureHeaderName = $properties['signature_header_name'] ?? '';

        if (! is_subclass_of($properties['signature_validator'], SignatureValidator::class)) {
            throw InvalidConfig::invalidSignatureValidator($properties['signature_validator']);
        }
        $this->signatureValidator = app($properties['signature_validator']);

        if (! is_subclass_of($properties['webhook_profile'], WebhookProfile::class)) {
            throw InvalidConfig::invalidWebhookProfile($properties['webhook_profile']);
        }
        $this->webhookProfile = app($properties['webhook_profile']);

        $webhookResponseClass = $properties['webhook_response'] ?? DefaultRespondsTo::class;
        if (! is_subclass_of($webhookResponseClass, RespondsToWebhook::class)) {
            throw InvalidConfig::invalidWebhookResponse($webhookResponseClass);
        }
        $this->webhookResponse = app($webhookResponseClass);

        $this->webhookEntity = $properties['webhook_entity'];

        $this->storeHeaders = $properties['store_headers'] ?? [];

        if (! is_subclass_of($properties['process_webhook_job'], ProcessWebhookJob::class)) {
            throw InvalidConfig::invalidProcessWebhookJob($properties['process_webhook_job']);
        }

        /** @var string $job */
        $job = $properties['process_webhook_job'];
        $this->processWebhookJobClass = $job;
    }
}
