<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Persistence;

use DateTimeImmutable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Throwable;
use WayOfDev\Cycle\Repository;
use WayOfDev\WebhookClient\Config;
use WayOfDev\WebhookClient\Contracts\WebhookCallRepository;
use WayOfDev\WebhookClient\Entities\Exception as ExceptionTypecast;
use WayOfDev\WebhookClient\Entities\Headers;
use WayOfDev\WebhookClient\Entities\Payload;
use WayOfDev\WebhookClient\Entities\WebhookCall;
use WayOfDev\WebhookClient\Exceptions\InvalidConfig;

use function array_map;
use function in_array;
use function is_int;
use function strtolower;

final class ORMWebhookCallRepository extends Repository implements WebhookCallRepository
{
    public function first(): ?WebhookCall
    {
        $entity = $this->select()
            ->orderBy('id')
            ->limit(1)
            ->fetchOne();

        return $entity instanceof WebhookCall ? $entity : null;
    }

    /**
     * @throws Throwable
     */
    public function store(Config $config, Request $request): WebhookCall
    {
        $headers = $this->headersToStore($config, $request);

        $entity = new WebhookCall(
            name: $config->name,
            url: $request->fullUrl(),
            headers: Headers::fromArray($headers),
            payload: Payload::fromArray($request->input()),
            exception: ExceptionTypecast::fromArray([]),
            createdAt: new DateTimeImmutable(),
        );

        $this->persist($entity);

        return $entity;
    }

    /**
     * @throws Throwable
     */
    public function storeException(WebhookCall $webhookCall, Exception $exception): void
    {
        $webhookCall->setException(ExceptionTypecast::fromArray([
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]));

        $this->persist($webhookCall);
    }

    /**
     * @throws Throwable
     */
    public function clearException(WebhookCall $webhookCall): void
    {
        $webhookCall->clearException();

        $this->persist($webhookCall);
    }

    /**
     * @throws InvalidConfig
     */
    public function prunable(): Collection
    {
        $days = config('webhook-client.delete_after_days');

        if (! is_int($days)) {
            throw InvalidConfig::invalidPrunable($days);
        }

        return new Collection($this->select()
            ->where('created_at', '<', now()->subDays($days))
            ->fetchAll());
    }

    private function headersToStore(Config $config, Request $request): array
    {
        $headerNamesToStore = $config->storeHeaders;

        if ('*' === $headerNamesToStore) {
            return $request->headers->all();
        }

        $headerNamesToStore = array_map(
            static fn (string $headerName) => strtolower($headerName),
            $headerNamesToStore,
        );

        return collect($request->headers->all())
            ->filter(fn (array $headerValue, string $headerName) => in_array($headerName, $headerNamesToStore, true))
            ->toArray();
    }
}
