<br>

<div align="center">
<img width="456" src="https://raw.githubusercontent.com/wayofdev/ansible-role-tpl/master/assets/logo.gh-light-mode-only.png#gh-light-mode-only">
<img width="456" src="https://raw.githubusercontent.com/wayofdev/ansible-role-tpl/master/assets/logo.gh-dark-mode-only.png#gh-dark-mode-only">
</div>


<br>

<br>

<div align="center">
<a href="https://github.com/wayofdev/laravel-webhook-client/actions"><img alt="Build Status" src="https://img.shields.io/endpoint.svg?url=https%3A%2F%2Factions-badge.atrox.dev%2Fwayofdev%2Flaravel-webhook-client%2Fbadge&style=flat-square"/></a>
<a href="https://packagist.org/packages/wayofdev/laravel-webhook-client"><img src="https://img.shields.io/packagist/dt/wayofdev/laravel-webhook-client?&style=flat-square" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/wayofdev/laravel-webhook-client"><img src="https://img.shields.io/packagist/v/wayofdev/laravel-webhook-client?&style=flat-square" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/wayofdev/laravel-webhook-client"><img src="https://img.shields.io/packagist/l/wayofdev/laravel-webhook-client?style=flat-square&color=blue" alt="Software License"/></a>
<a href="https://packagist.org/packages/wayofdev/laravel-webhook-client"><img alt="Commits since latest release" src="https://img.shields.io/github/commits-since/wayofdev/laravel-webhook-client/latest?style=flat-square"></a>
</div>
<br>

# Receive webhooks in Laravel apps

Webhooks offer a mechanism for one application to inform another about specific events, typically using a straightforward HTTP request.

The [wayofdev/laravel-webhook-client](https://github.com/wayofdev/laravel-webhook-client) package facilitates the reception of webhooks in a Laravel application, leveraging the power of cycle-orm. Features include verifying signed calls, storing payload data, and processing the payloads in a queued job.

This package is inspired by and re-written from the original [spatie/laravel-webhook-client](https://github.com/spatie/laravel-webhook-client) to incorporate [Cycle-ORM](https://cycle-orm.dev) support.

<br>

## 💿 Installation

### → Using composer

Require as dependency:

```bash
$ composer req wayofdev/laravel-webhook-client
```

<br>

### → Configuring the package

You can publish the config file with:

```bash
php artisan vendor:publish \
	--provider="WayOfDev\WebhookClient\Bridge\Laravel\Providers\WebhookClientServiceProvider" \
	--tag="config"
```

This is the contents of the file that will be published at `config/webhook-client.php`:

```php
<?php

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
```

In the `signing_secret` key of the config file, you should add a valid webhook secret. This value should be provided by the app that will send you webhooks.

This package will try to store and respond to the webhook as fast as possible. Processing the payload of the request is done via a queued job. It's recommended to not use the `sync` driver but a real queue driver. You should specify the job that will handle processing webhook requests in the `process_webhook_job` of the config file. A valid job is any class that extends `WayOfDev\WebhookClient\Bridge\Laravel\Jobs\ProcessWebhookJob` and has a `handle` method.

<br>

### → Preparing the database

By default, all webhook calls will get saved in the database.

To create the table that holds the webhook calls: 

1. You must have already configured and running  [wayofdev/laravel-cycle-orm-adapter](https://github.com/wayofdev/laravel-cycle-orm-adapter) package in your Laravel project.

2. Edit `cycle.php` config to add WebhookCall entity to search paths:

   ```php
   // ...
   
   'tokenizer' => [
       /*
        * Where should class locator scan for entities?
        */
       'directories' => [
           __DIR__ . '/../src/Domain', // Your current project Entities
           __DIR__ . '/../vendor/wayofdev/laravel-webhook-client/src/Entities', // Register new Entity
       ],
     
     	// ...
   ],
   ```

3. After editing config, run command to generate new migrations from newly appeared entity:

   ```bash
   $ php artisan cycle:orm:migrate
   ```

   **(Optional):** To view list of migrations, to be executed:

   ```bash
   $ php artisan cycle:migrate:status
   ```

4. Run outstanding migrations using command:

   ```bash
   $ php artisan cycle:migrate
   ```

   <br>

### → Taking care of routing

Finally, let's take care of the routing. At the app that sends webhooks, you probably configure an URL where you want your webhook requests to be sent. In the routes file of your app, you must pass that route to `Route::webhooks`. Here's an example:

```php
Route::webhooks('webhook-receiving-url');
```

Behind the scenes, by default this will register a `POST` route to a controller provided by this package. Because the app that sends webhooks to you has no way of getting a csrf-token, you must add that route to the `except` array of the `VerifyCsrfToken` middleware:

```php
protected $except = [
    'webhook-receiving-url',
];
```

<br>

## 💻 Usage

Once you've completed the installation, here's a comprehensive breakdown of how the package functions:

1. **Signature Verification:**
   * The package initiates by verifying the incoming request's signature.
   * If the signature doesn't pass the verification, an exception is thrown, the `InvalidSignatureEvent` event is fired, and the request isn't saved to the database.
2. **Webhook Profile Evaluation:**
   * Each incoming request interacts with a webhook profile, which is essentially a class that evaluates if a request should be both saved and processed within your application.
   * This profile enables filtering of specific webhook requests based on the app's requirements.
   * [Your own custom webhook profile](#determining-which-webhook-requests-should-be-stored-and-processed) can be created, to change or extend this logic.
3. **Storage & Processing:**
   * If the profile gives the go-ahead, the request is first saved in the `webhook_calls` table.
   * Subsequently, a queued job handles the `WebhookCall` entity.
   * Webhooks usually expect a fast response, so by queuing jobs, we can respond quickly.
   * Configuration for the job processing the webhook is found under the `process_webhook_job` in the `webhook-client` config file.
   * If any issues arise during job queuing, the package logs the exception within the `exception` field of the `WebhookCall` entity. 
4. **Webhook Response:**
   * Once the job is dispatched, a webhook response takes charge. This class determines the HTTP response for the request.
   * By default, a `200` status code with an 'ok' message is returned. However, you can also craft a custom webhook response. Learn how to easly [create your own webhook response](#creating-your-own-webhook-response).

<br>

### → Verifying the signature of incoming webhooks

This package assumes that an incoming webhook request has a header that can be used to verify the payload has not been tampered with. The name of the header containing the signature can be configured in the `signature_header_name` key of the config file. By default, the package uses the `DefaultSignatureValidator` to validate signatures. This is how that class will compute the signature.

```php
$computedSignature = hash_hmac(
  'sha256',
  $request->getContent(),
  $configuredSigningSecret
);
```

If the `$computedSignature` does match the value, the request will be [passed to the webhook profile](#determining-which-webhook-requests-should-be-stored-and-processed). If `$computedSignature` does not match the value in the signature header, the package will respond with a `500` and discard the request.

### → Creating your own signature validator

A signature validator is any class that implements `WayOfDev\WebhookClient\Contracts\SignatureValidator`. Here's what that interface looks like.

```php
<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Contracts;

use Illuminate\Http\Request;
use WayOfDev\WebhookClient\Config;

interface SignatureValidator
{
    public function isValid(Request $request, Config $config): bool;
}
```

`WebhookConfig` is a data transfer object that lets you easily pull up the config (containing the header name that contains the signature and the secret) for the webhook request.

After creating your own `SignatureValidator` you must register it in the `signature_validator` in the `webhook-client` config file.

### → Determining which webhook requests should be stored and processed

After the signature of an incoming webhook request is validated, the request will be passed to a webhook profile. A webhook profile is a class that determines if the request should be stored and processed. If the webhook sending app sends out request where your app isn't interested in, you can use this class to filter out such events.

By default the `\WayOfDev\WebhookClient\Profile\ProcessEverythingWebhookProfile` class is used. As its name implies, this default class will determine that all incoming requests should be stored and processed.

### → Creating your own webhook profile

A webhook profile is any class that implements `\WayOfDev\WebhookClient\Contracts\WebhookProfile`. This is what that interface looks like:

```php
<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Contracts;

use Illuminate\Http\Request;

interface WebhookProfile
{
    public function shouldProcess(Request $request): bool;
}
```

After creating your own `WebhookProfile` you must register it in the `webhook_profile` key in the `webhook-client` config file.

### → Storing and processing webhooks

After the signature is validated and the webhook profile has determined that the request should be processed, the package will store and process the request.

The request will first be stored in the `webhook_calls` table, involving the `WebhookCall` entity and the `WebhookCallRepository`.

Should you want to customize the table name or anything on the storage behavior, the package grants flexibility to employ an alternative entity.This can be done by setting the desired entity in the `webhook_entity`.

Ensure your entity is derived from `WayOfDev\WebhookClient\Entities\WebhookCall`. For storage purposes, the `WebhookCallRepository` interface alongside its implementation, `ORMWebhookCallRepository`, come into play. Modifications to the default repository can be made through `webhook_entity_repository`.

You can change how the webhook is stored by overriding the `store` method of `ORMWebhookCallRepository` In the `store` method you should return persisted entity.

Next, the newly created `WebhookCall` entity will be passed to a queued job that will process the request. Any class that extends `\WayOfDev\WebhookClient\Bridge\Laravel\Jobs\ProcessWebhookJob` is a valid job. Here's an example:

```php
<?php

declare(strict_types=1);

namespace Infrastructure\Jobs;

use WayOfDev\WebhookClient\Bridge\Laravel\Jobs\ProcessWebhookJob as AbstractProcessWebhookJob;

class ProcessWebhookJob extends AbstractProcessWebhookJob
{
    public function handle()
    {
        // $this->webhookCall // contains an instance of `WebhookCall`

        // perform the work here
    }
}
```

You should specify the class name of your job in the `process_webhook_job` of the `webhook-client` config file.

<br>

### → Creating your own webhook response

A webhook response is any class that implements `\WayOfDev\WebhookClient\Contracts\RespondsToWebhook`. This is what that interface looks like:

```php
<?php

declare(strict_types=1);

namespace WayOfDev\WebhookClient\Contracts;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use WayOfDev\WebhookClient\Config;

interface RespondsToWebhook
{
    public function respondToValidWebhook(Request $request, Config $config): Response;
}
```

After creating your own `WebhookResponse` you must register it in the `webhook_response` key in the `webhook-client` config file.

<br>

### → Handling incoming webhook request for multiple apps

This package allows webhooks to be received from multiple different apps. Let's take a look at an example config file where we add support for two webhook URLs. All comments from the config have been removed for brevity.

```php
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
            'name' => 'webhook-sending-app-1',
            'signing_secret' => 'secret-for-webhook-sending-app-1',
            'signature_header_name' => 'Signature-for-app-1',
            'signature_validator' => DefaultSignatureValidator::class,
            'webhook_profile' => ProcessEverythingWebhookProfile::class,
            'webhook_response' => DefaultRespondsTo::class,
            'webhook_entity' => WebhookCall::class,
            'webhook_entity_repository' => ORMWebhookCallRepository::class,
            'process_webhook_job' => '',
        ],
        [
            'name' => 'webhook-sending-app-2',
            'signing_secret' => 'secret-for-webhook-sending-app-2',
            'signature_header_name' => 'Signature-for-app-2',
            'signature_validator' => DefaultSignatureValidator::class,
            'webhook_profile' => ProcessEverythingWebhookProfile::class,
            'webhook_response' => DefaultRespondsTo::class,
            'webhook_entity' => WebhookCall::class,
            'webhook_entity_repository' => ORMWebhookCallRepository::class,
            'process_webhook_job' => '',
        ],
    ],
];
```

When registering routes for the package, you should pass the `name` of the config as a second parameter.

```php
Route::webhooks('receiving-url-for-app-1', 'webhook-sending-app-1');
Route::webhooks('receiving-url-for-app-2', 'webhook-sending-app-2');
```

<br>

### → Change route method

Being an incoming webhook client, there are instances where you might want to establish a route method other than the default `post`. You have the flexibility to modify the standard post method to options such as `get`, `put`, `patch`, or `delete`.

```php
Route::webhooks('receiving-url-for-app-1', 'webhook-sending-app-1', 'get');
Route::webhooks('receiving-url-for-app-1', 'webhook-sending-app-1', 'put');
Route::webhooks('receiving-url-for-app-1', 'webhook-sending-app-1', 'patch');
Route::webhooks('receiving-url-for-app-1', 'webhook-sending-app-1', 'delete');
```

<br>

### → Using the package without a controller

If you don't want to use the routes and controller provided by your macro, you can programmatically add support for webhooks to your own controller.

`WayOfDev\WebhookClient\WebhookProcessor` is a class that verifies the signature, calls the web profile, stores the webhook request, and starts a queued job to process the stored webhook request. The controller provided by this package also uses that class [under the hood](https://github.com/wayofdev/laravel-webhook-client/blob/56167f0be276f41b947cc6c7a5bd30230b8a08d6/src/Bridge/Laravel/Http/Controllers/WebhookController.php#L25).

It can be used like this:

```php
use WayOfDev\WebhookClient\Entities\WebhookCall;
use WayOfDev\WebhookClient\Persistence\ORMWebhookCallRepository;
use WayOfDev\WebhookClient\Profile\ProcessEverythingWebhookProfile;
use WayOfDev\WebhookClient\Response\DefaultRespondsTo;
use WayOfDev\WebhookClient\SignatureValidator\DefaultSignatureValidator;
use WayOfDev\WebhookClient\Config;
use WayOfDev\WebhookClient\WebhookProcessor;

$webhookConfig = new Config([
    'name' => 'webhook-sending-app-1',
    'signing_secret' => 'secret-for-webhook-sending-app-1',
    'signature_header_name' => 'Signature',
    'signature_validator' => DefaultSignatureValidator::class,
    'webhook_profile' => ProcessEverythingWebhookProfile::class,
    'webhook_response' => DefaultRespondsTo::class,
    'webhook_entity' => WebhookCall::class,
    'webhook_entity_repository' => ORMWebhookCallRepository::class,
    'process_webhook_job' => '',
]);

(new WebhookProcessor($request, $webhookConfig))->process();
```

<br>

### → Deleting entities

Whenever a webhook comes in, this package will store as a `WebhookCall` entity. After a while, you might want to delete old entities.

@todo Laravel version uses mass-prunable trait, so, entity deletion logic should be re-written using laravel console commands or by commiting to cycle-orm.

In this example all entities will be deleted when older than 30 days.

```php
return [
    'configs' => [
        // ...
    ],

    'delete_after_days' => 30,
];
```

<br>

## 🧪 Running Tests

### → PHPUnit tests

To run tests, run the following command:

```bash
$ make test
```

### → Static Analysis

Code quality using PHPStan:

```bash
$ make lint-stan
```

### → Coding Standards Fixing

Fix code using The PHP Coding Standards Fixer (PHP CS Fixer) to follow our standards:

```bash
$ make lint-php
```

<br>

## 🤝 License

[![Licence](https://img.shields.io/github/license/wayofdev/laravel-webhook-client?style=for-the-badge&color=blue)](./LICENSE)

<br>

## 🧱 Credits and Useful Resources

This repository is based on the [spatie/laravel-webhook-client](https://github.com/spatie/laravel-webhook-client) work.

<br>

## 🙆🏼‍♂️ Author Information

Created in **2023** by [lotyp / wayofdev](https://github.com/wayofdev)

<br>

## 🙌 Want to Contribute?

Thank you for considering contributing to the wayofdev community! We are open to all kinds of contributions. If you want to:

- 🤔 Suggest a feature
- 🐛 Report an issue
- 📖 Improve documentation
- 👨‍💻 Contribute to the code

