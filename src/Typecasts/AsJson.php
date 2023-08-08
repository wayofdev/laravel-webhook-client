<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Typecasts;

use Cycle\Database\DatabaseInterface;
use JsonException;
use Stringable;

use function json_decode;
use function json_encode;

/**
 * @phpstan-consistent-constructor
 */
abstract class AsJson implements Stringable
{
    public static function fromArray(array $value): static
    {
        return new static($value);
    }

    /**
     * @throws JsonException
     */
    public static function castValue(mixed $value, DatabaseInterface $db): static
    {
        return static::fromArray(
            json_decode($value, true, 512, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @throws JsonException
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @throws JsonException
     */
    public function toString(): string
    {
        return json_encode($this->value, JSON_THROW_ON_ERROR);
    }

    public function toArray(): array
    {
        return $this->value;
    }

    protected function __construct(
        protected readonly array $value
    ) {
    }
}
