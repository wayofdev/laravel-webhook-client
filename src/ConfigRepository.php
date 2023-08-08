<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient;

class ConfigRepository
{
    /** @var Config[] */
    private array $configs;

    public function addConfig(Config $webhookConfig): void
    {
        $this->configs[$webhookConfig->name] = $webhookConfig;
    }

    public function getConfig(string $name): ?Config
    {
        return $this->configs[$name] ?? null;
    }
}
