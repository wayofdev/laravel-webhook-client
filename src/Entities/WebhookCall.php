<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Entities;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\ORM\Entity\Behavior;
use DateTimeImmutable;
use Exception;
use Symfony\Component\HttpFoundation\HeaderBag;
use WayOfDev\WebhookClient\Contracts\WebhookCallRepository;
use WayOfDev\WebhookClient\Typecasts\AsJson;

#[Entity(role: 'webhook_call', repository: WebhookCallRepository::class, table: 'webhook_calls')]
#[Behavior\CreatedAt(field: 'createdAt', column: 'created_at')]
#[Behavior\UpdatedAt(field: 'updatedAt', column: 'updated_at')]
class WebhookCall
{
    #[Column(type: 'primary')]
    public int $id;

    #[Column(type: 'string')]
    private string $name;

    #[Column(type: 'string')]
    private string $url;

    #[Column(type: 'json', typecast: [AsJson::class, 'castValue'])]
    private array $headers;

    #[Column(type: 'json', typecast: [AsJson::class, 'castValue'])]
    private array $payload;

    #[Column(type: 'json', nullable: true, typecast: [AsJson::class, 'castValue'])]
    private ?array $exception;

    #[Column(type: 'datetime')]
    private DateTimeImmutable $createdAt;

    #[Column(type: 'datetime')]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $name,
        string $url,
        array $headers,
        array $payload,
        ?array $exception,
        DateTimeImmutable $createdAt,
    ) {
        $this->name = $name;
        $this->url = $url;
        $this->headers = $headers;
        $this->payload = $payload;
        $this->exception = $exception;

        $this->createdAt = $createdAt;
        $this->updatedAt = clone $createdAt;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function headers(): HeaderBag
    {
        return $this->headerBag();
    }

    public function headerBag(): HeaderBag
    {
        return new HeaderBag($this->headers ?? []);
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function exception(): array
    {
        return $this->exception;
    }

    public function setException(Exception $exception): void
    {
        $this->exception = [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ];
    }

    public function clearException(): void
    {
        $this->exception = null;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
