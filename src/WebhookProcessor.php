<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient;

use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use WayOfDev\WebhookClient\Bridge\Laravel\Events\InvalidWebhookSignatureEvent;
use WayOfDev\WebhookClient\Contracts\WebhookCallRepository;
use WayOfDev\WebhookClient\Entities\WebhookCall;
use WayOfDev\WebhookClient\Exceptions\InvalidWebhookSignature;

class WebhookProcessor
{
    public function __construct(
        protected Request $request,
        protected WebhookConfig $config,
        protected WebhookCallRepository $repository
    ) {
    }

    /**
     * @throws InvalidWebhookSignature
     * @throws Exception
     */
    public function process(): Response
    {
        $this->ensureValidSignature();

        if (! $this->config->webhookProfile->shouldProcess($this->request)) {
            return $this->createResponse();
        }

        $webhookCall = $this->storeWebhook();

        $this->processWebhook($webhookCall);

        return $this->createResponse();
    }

    /**
     * @throws InvalidWebhookSignature
     */
    protected function ensureValidSignature(): self
    {
        if (! $this->config->signatureValidator->isValid($this->request, $this->config)) {
            event(new InvalidWebhookSignatureEvent($this->request));

            throw InvalidWebhookSignature::make();
        }

        return $this;
    }

    protected function storeWebhook(): WebhookCall
    {
        return $this->repository->store($this->config, $this->request);
    }

    /**
     * @throws Exception
     */
    protected function processWebhook(WebhookCall $webhookCall): void
    {
        try {
            $job = new $this->config->processWebhookJobClass($webhookCall);

            $this->repository->clearException($webhookCall);

            dispatch($job);
        } catch (Exception $exception) {
            $this->repository->storeException($webhookCall, $exception);

            throw $exception;
        }
    }

    protected function createResponse(): Response
    {
        return $this->config->webhookResponse->respondToValidWebhook($this->request, $this->config);
    }
}
