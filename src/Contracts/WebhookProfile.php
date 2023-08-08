<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Contracts;

use Illuminate\Http\Request;

interface WebhookProfile
{
    public function shouldProcess(Request $request): bool;
}
