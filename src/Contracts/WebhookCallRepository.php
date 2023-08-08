<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Contracts;

use Cycle\ORM\RepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use WayOfDev\WebhookClient\Config;
use WayOfDev\WebhookClient\Entities\WebhookCall;

/**
 * @template TEntity of WebhookCall
 */
interface WebhookCallRepository extends RepositoryInterface
{
    public function first(): ?WebhookCall;

    public function store(Config $config, Request $request): WebhookCall;

    public function storeException(WebhookCall $webhookCall, Exception $exception): void;

    public function clearException(WebhookCall $webhookCall): void;

    public function prunable(): Collection;
}
