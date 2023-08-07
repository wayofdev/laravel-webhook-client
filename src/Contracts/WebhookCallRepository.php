<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Contracts;

use Cycle\ORM\RepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use WayOfDev\WebhookClient\Entities\WebhookCall;
use WayOfDev\WebhookClient\WebhookConfig;

interface WebhookCallRepository extends RepositoryInterface
{
    public function first(): ?WebhookCall;

    public function store(WebhookConfig $config, Request $request): WebhookCall;

    public function storeException(WebhookCall $webhookCall, Exception $exception): void;

    public function clearException(WebhookCall $webhookCall): void;

    public function prunable(): Collection;
}
