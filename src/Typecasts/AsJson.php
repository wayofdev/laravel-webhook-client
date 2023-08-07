<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Typecasts;

use Cycle\Database\DatabaseInterface;
use JsonException;
use Stringable;

use function json_decode;
use function json_encode;

readonly class AsJson implements Stringable
{
    /**
     * @throws JsonException
     */
    public static function castValue(mixed $value, DatabaseInterface $db): array
    {
        if (null === $value) {
            return [];
        }

        return json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR);
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

    private function __construct(
        private array $value
    ) {
    }
}
