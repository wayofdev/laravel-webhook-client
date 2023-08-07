<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Tests\Bridge\Laravel\Http\Controllers;

use Cycle\ORM\ORMInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use JsonException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use WayOfDev\WebhookClient\Bridge\Laravel\Events\InvalidWebhookSignatureEvent;
use WayOfDev\WebhookClient\Contracts\WebhookCallRepository;
use WayOfDev\WebhookClient\Entities\WebhookCall;
use WayOfDev\WebhookClient\Exceptions\InvalidConfig;
use WayOfDev\WebhookClient\Tests\TestCase;
use WayOfDev\WebhookClient\Tests\TestClasses\Jobs\ProcessWebhookJobTestClass;
use WayOfDev\WebhookClient\Tests\TestClasses\Profile\ProcessNothingWebhookProfile;
use WayOfDev\WebhookClient\Tests\TestClasses\Responses\CustomRespondsToWebhook;
use WayOfDev\WebhookClient\Tests\TestClasses\SignatureValidator\EverythingIsValidSignatureValidator;
use WayOfDev\WebhookClient\Tests\TestClasses\SignatureValidator\NothingIsValidSignatureValidator;
// use WayOfDev\WebhookClient\Tests\TestClasses\WebhookModelWithoutPayloadSaved;
use WayOfDev\WebhookClient\WebhookConfig;
use WayOfDev\WebhookClient\WebhookConfigRepository;

use function count;
use function hash_hmac;
use function json_encode;

final class WebhookControllerTest extends TestCase
{
    private array $payload;

    private array $headers;

    private WebhookCallRepository $repository;

    /**
     * @throws JsonException
     */
    public function setUp(): void
    {
        parent::setUp();

        config()->set('webhook-client.configs.0.signing_secret', 'abc123');
        config()->set('webhook-client.configs.0.process_webhook_job', ProcessWebhookJobTestClass::class);

        Route::webhooks('incoming-webhooks');
        Queue::fake();
        Event::fake();

        $this->repository = app(ORMInterface::class)->getRepository(WebhookCall::class);

        $this->payload = ['a' => 1];

        $this->headers = [
            config('webhook-client.configs.0.signature_header_name') => $this->determineSignature($this->payload),
        ];
    }

    /** @test */
    public function it_can_process_a_webhook_request(): void
    {
        $this->withoutExceptionHandling();

        $this
            ->postJson('incoming-webhooks', $this->payload, $this->headers)
            ->assertSuccessful();

        $this::assertCount(1, $this->repository->findAll());

        $webhookCall = $this->repository->first();
        $this::assertEquals('default', $webhookCall->name());
        $this::assertEquals(['a' => 1], $webhookCall->payload());

        Queue::assertPushed(ProcessWebhookJobTestClass::class, function (ProcessWebhookJobTestClass $job) {
            $this::assertEquals(1, $job->webhookCall->id);

            return true;
        });
    }

    /** @test */
    public function a_request_with_an_invalid_payload_will_not_get_processed(): void
    {
        $headers = $this->headers;
        $headers['Signature'] .= 'invalid';

        $this
            ->postJson('incoming-webhooks', $this->payload, $headers)
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

        $this::assertCount(0, $this->repository->findAll());
        Queue::assertNothingPushed();
        Event::assertDispatched(InvalidWebhookSignatureEvent::class);
    }

    /** @test
     * @throws InvalidConfig
     */
    public function it_can_work_with_an_alternative_signature_validator(): void
    {
        config()->set('webhook-client.configs.0.signature_validator', EverythingIsValidSignatureValidator::class);
        $this->refreshWebhookConfigRepository();

        $this
            ->postJson('incoming-webhooks', $this->payload, [])
            ->assertStatus(200);

        config()->set('webhook-client.configs.0.signature_validator', NothingIsValidSignatureValidator::class);
        $this->refreshWebhookConfigRepository();

        $this
            ->postJson('incoming-webhooks', $this->payload, [])
            ->assertStatus(500);
    }

    /** @test
     * @throws InvalidConfig
     */
    public function it_can_work_with_an_alternative_profile(): void
    {
        config()->set('webhook-client.configs.0.webhook_profile', ProcessNothingWebhookProfile::class);
        $this->refreshWebhookConfigRepository();

        $this
            ->postJson('incoming-webhooks', $this->payload, $this->headers)
            ->assertSuccessful();

        Queue::assertNothingPushed();
        Event::assertNotDispatched(InvalidWebhookSignatureEvent::class);
        $this::assertCount(0, $this->repository->findAll());
    }

    /** @test
     * @throws InvalidConfig
     */
    public function it_can_work_with_an_alternative_config(): void
    {
        Route::webhooks('incoming-webhooks-alternative-config', 'alternative-config');

        $this
            ->postJson('incoming-webhooks-alternative-config', $this->payload, $this->headers)
            ->assertStatus(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);

        config()->set('webhook-client.configs.0.name', 'alternative-config');
        $this->refreshWebhookConfigRepository();

        $this
            ->postJson('incoming-webhooks-alternative-config', $this->payload, $this->headers)
            ->assertSuccessful();
    }

    //    /** @test
    //     * @throws InvalidConfig
    //     */
    //    public function it_can_work_with_an_alternative_model(): void
    //    {
    //        $this->withoutExceptionHandling();
    //
    //        config()->set('webhook-client.configs.0.webhook_model', WebhookModelWithoutPayloadSaved::class);
    //        $this->refreshWebhookConfigRepository();
    //
    //        $this
    //            ->postJson('incoming-webhooks', $this->payload, $this->headers)
    //            ->assertSuccessful();
    //
    //        $this::assertCount(1, $this->repository->findAll());
    //        $this::assertEquals([], $this->repository->first()->payload);
    //    }

    /** @test */
    public function it_can_respond_with_custom_response(): void
    {
        config()->set('webhook-client.configs.0.webhook_response', CustomRespondsToWebhook::class);

        $this
            ->postJson('incoming-webhooks', $this->payload, $this->headers)
            ->assertSuccessful()
            ->assertJson([
                'foo' => 'bar',
            ]);
    }

    /** @test */
    public function it_can_store_a_specific_header(): void
    {
        $this->withoutExceptionHandling();

        config()->set('webhook-client.configs.0.store_headers', ['Signature']);

        $this
            ->postJson('incoming-webhooks', $this->payload, $this->headers)
            ->assertSuccessful();

        $this::assertCount(1, $this->repository->findAll());
        $this::assertCount(1, $this->repository->first()->headers());
        $this::assertEquals($this->headers['Signature'], $this->repository->first()->headerBag()->get('Signature'));
    }

    /** @test */
    public function it_can_store_all_headers(): void
    {
        $this->withoutExceptionHandling();

        config()->set('webhook-client.configs.0.store_headers', '*');

        $this
            ->postJson('incoming-webhooks', $this->payload, $this->headers)
            ->assertSuccessful();

        $this::assertCount(1, $this->repository->findAll());
        $this::assertGreaterThan(1, count($this->repository->first()->headers()));
    }

    /** @test */
    public function it_can_store_none_of_the_headers(): void
    {
        $this->withoutExceptionHandling();

        config()->set('webhook-client.configs.0.store_headers', []);

        $this
            ->postJson('incoming-webhooks', $this->payload, $this->headers)
            ->assertSuccessful();

        $this::assertCount(1, $this->repository->findAll());
        $this::assertCount(0, $this->repository->first()->headers());
    }

    /**
     * @throws JsonException
     */
    private function determineSignature(array $payload): string
    {
        $secret = config('webhook-client.configs.0.signing_secret');

        return hash_hmac('sha256', json_encode($payload, JSON_THROW_ON_ERROR), $secret);
    }

    /**
     * @throws InvalidConfig
     */
    private function refreshWebhookConfigRepository(): void
    {
        $webhookConfig = new WebhookConfig(config('webhook-client.configs.0'));

        app(WebhookConfigRepository::class)->addConfig($webhookConfig);
    }
}
