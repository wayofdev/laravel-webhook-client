<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Tests;

use WayOfDev\WebhookClient\App\Jobs\ProcessWebhookJobTestClass;
use WayOfDev\WebhookClient\Config;
use WayOfDev\WebhookClient\Entities\WebhookCall;
use WayOfDev\WebhookClient\Exceptions\InvalidConfig;
use WayOfDev\WebhookClient\Profile\ProcessEverythingWebhookProfile;
use WayOfDev\WebhookClient\Response\DefaultRespondsTo;
use WayOfDev\WebhookClient\SignatureValidator\DefaultSignatureValidator;

class WebhookConfigTest extends TestCase
{
    /**
     * @test
     *
     * @throws InvalidConfig
     */
    public function it_can_handle_a_valid_configuration(): void
    {
        $configArray = $this->getValidConfig();

        $webhookConfig = new Config($configArray);

        $this::assertEquals($configArray['name'], $webhookConfig->name);
        $this::assertEquals($configArray['signing_secret'], $webhookConfig->signingSecret);
        $this::assertEquals($configArray['signature_header_name'], $webhookConfig->signatureHeaderName);
        $this::assertInstanceOf($configArray['signature_validator'], $webhookConfig->signatureValidator);
        $this::assertInstanceOf($configArray['webhook_profile'], $webhookConfig->webhookProfile);
        $this::assertEquals($configArray['webhook_entity'], $webhookConfig->webhookEntity);
        $this::assertEquals($configArray['process_webhook_job'], $webhookConfig->processWebhookJobClass);
    }

    /**
     * @test
     */
    public function it_validates_the_signature_validator(): void
    {
        $config = $this->getValidConfig();
        $config['signature_validator'] = 'invalid-signature-validator';

        $this->expectException(InvalidConfig::class);

        new Config($config);
    }

    /**
     * @test
     */
    public function it_validates_the_webhook_profile(): void
    {
        $config = $this->getValidConfig();
        $config['webhook_profile'] = 'invalid-webhook-profile';

        $this->expectException(InvalidConfig::class);

        new Config($config);
    }

    /**
     * @test
     */
    public function it_validates_the_webhook_response(): void
    {
        $config = $this->getValidConfig();
        $config['webhook_response'] = 'invalid-webhook-response';

        $this->expectException(InvalidConfig::class);

        new Config($config);
    }

    /**
     * @test
     *
     * @throws InvalidConfig
     */
    public function it_uses_the_default_webhook_response_if_none_provided(): void
    {
        $config = $this->getValidConfig();
        $config['webhook_response'] = null;

        $this::assertInstanceOf(DefaultRespondsTo::class, (new Config($config))->webhookResponse);
    }

    /**
     * @test
     */
    public function it_validates_the_process_webhook_job(): void
    {
        $config = $this->getValidConfig();
        $config['process_webhook_job'] = 'invalid-process-webhook-job';

        $this->expectException(InvalidConfig::class);

        new Config($config);
    }

    protected function getValidConfig(): array
    {
        return [
            'name' => 'default',
            'signing_secret' => 'my-secret',
            'signature_header_name' => 'Signature',
            'signature_validator' => DefaultSignatureValidator::class,
            'webhook_profile' => ProcessEverythingWebhookProfile::class,
            'webhook_response' => DefaultRespondsTo::class,
            'webhook_entity' => WebhookCall::class,
            'process_webhook_job' => ProcessWebhookJobTestClass::class,
        ];
    }
}
