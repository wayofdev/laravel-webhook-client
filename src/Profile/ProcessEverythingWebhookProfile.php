<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Profile;

use Illuminate\Http\Request;

class ProcessEverythingWebhookProfile implements WebhookProfile
{
    public function shouldProcess(Request $request): bool
    {
        return true;
    }
}
