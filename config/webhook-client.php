<?php

declare(strict_types=1);

use WayOfDev\WebhookClient\Entities\WebhookCall;
use WayOfDev\WebhookClient\Persistence\ORMWebhookCallRepository;
use WayOfDev\WebhookClient\Profile\ProcessEverythingWebhookProfile;
use WayOfDev\WebhookClient\Response\DefaultRespondsTo;
use WayOfDev\WebhookClient\SignatureValidator\DefaultSignatureValidator;

return [
    'configs' => [
        [
            /*
             * This package supports multiple webhook receiving endpoints. If you only have
             * one endpoint receiving webhooks, you can use 'default'.
             */
            'name' => 'default',

            /*
             * We expect that every webhook call will be signed using a secret. This secret
             * is used to verify that the payload has not been tampered with.
             */
            'signing_secret' => env('WEBHOOK_CLIENT_SECRET'),

            /*
             * The name of the header containing the signature.
             */
            'signature_header_name' => 'Signature',

            /*
             * This class will verify that the content of the signature header is valid.
             *
             * It should implement \WayOfDev\WebhookClient\Contracts\SignatureValidator
             */
            'signature_validator' => DefaultSignatureValidator::class,

            /*
             * This class determines if the webhook call should be stored and processed.
             */
            'webhook_profile' => ProcessEverythingWebhookProfile::class,

            /*
             * This class determines the response on a valid webhook call.
             */
            'webhook_response' => DefaultRespondsTo::class,

            /*
             * The classname of the entity to be used to store webhook calls. The class should
             * be equal or extend WayOfDev\WebhookClient\Entities\WebhookCall.
             */
            'webhook_entity' => WebhookCall::class,

            /*
             * The classname of the repository to be used to store webhook calls. The class should
             * implement WayOfDev\WebhookClient\Contracts\WebhookCallRepository.
             */
            'webhook_entity_repository' => ORMWebhookCallRepository::class,

            /*
             * In this array, you can pass the headers that should be stored on
             * the webhook call entity when a webhook comes in.
             *
             * To store all headers, set this value to `*`.
             */
            'store_headers' => [
                '*',
            ],

            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \WayOfDev\WebhookClient\Jobs\ProcessWebhookJob.
             */
            'process_webhook_job' => '',
        ],
    ],

    /*
     * The integer amount of days after which database records should be deleted.
     *
     * 7 deletes all records after 1 week. Set to null if no database records should be deleted.
     */
    'delete_after_days' => 30,
];
