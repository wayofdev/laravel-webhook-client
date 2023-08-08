<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Tests\TestClasses\Profile;

use Illuminate\Http\Request;
use WayOfDev\WebhookClient\Contracts\WebhookProfile;

class ProcessNothingWebhookProfile implements WebhookProfile
{
    public function shouldProcess(Request $request): bool
    {
        return false;
    }
}
