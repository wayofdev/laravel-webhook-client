<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\App\Entities;

use Cycle\Annotated\Annotation\Entity;
use WayOfDev\WebhookClient\Contracts\WebhookCallRepository;
use WayOfDev\WebhookClient\Entities\Payload;
use WayOfDev\WebhookClient\Entities\WebhookCall;

#[Entity(role: 'extended_webhook_call', repository: WebhookCallRepository::class)]
class WebhookEntityWithoutPayloadSaved extends WebhookCall
{
    public function payload(): Payload
    {
        return Payload::fromArray([]);
    }
}
