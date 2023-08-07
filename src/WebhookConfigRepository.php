<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient;

class WebhookConfigRepository
{
    /** @var WebhookConfig[] */
    protected array $configs;

    public function addConfig(WebhookConfig $webhookConfig): void
    {
        $this->configs[$webhookConfig->name] = $webhookConfig;
    }

    public function getConfig(string $name): ?WebhookConfig
    {
        return $this->configs[$name] ?? null;
    }
}
