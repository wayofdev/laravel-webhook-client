<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Exceptions;

use Exception;

final class InvalidMethod extends Exception
{
    public static function make($method): self
    {
        return new self("The method $method is not allowed.");
    }
}
