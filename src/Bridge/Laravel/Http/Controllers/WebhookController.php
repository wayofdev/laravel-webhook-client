<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Bridge\Laravel\Http\Controllers;

use Cycle\ORM\ORMInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use WayOfDev\WebhookClient\Config;
use WayOfDev\WebhookClient\Contracts\WebhookCallRepository;
use WayOfDev\WebhookClient\Exceptions\InvalidWebhookSignature;
use WayOfDev\WebhookClient\WebhookProcessor;

class WebhookController
{
    /**
     * @throws InvalidWebhookSignature
     */
    public function __invoke(Request $request, Config $config, ORMInterface $orm): Response
    {
        /** @var WebhookCallRepository $repository */
        $repository = $orm->getRepository($config->webhookEntity);

        return (new WebhookProcessor($request, $config, $repository))->process();
    }
}
