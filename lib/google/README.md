Google Api Client Wrapper
=========

> Google api php client wrapper with Cloud Platform and Laravel support

[![Latest Stable Version](https://poser.pugx.org/pulkitjalan/google-apiclient/v/stable?format=flat-square)](https://packagist.org/packages/pulkitjalan/google-apiclient)
[![MIT License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](http://www.opensource.org/licenses/MIT)
[![Run Tests](https://github.com/pulkitjalan/google-apiclient/actions/workflows/run-tests.yml/badge.svg)](https://github.com/pulkitjalan/google-apiclient/actions/workflows/run-tests.yml)
[![Coverage](https://codecov.io/gh/pulkitjalan/google-apiclient/graph/badge.svg?token=dpjZ4Tszxm)](https://codecov.io/gh/pulkitjalan/google-apiclient)
[![Total Downloads](https://img.shields.io/packagist/dt/pulkitjalan/google-apiclient.svg?style=flat-square)](https://packagist.org/packages/pulkitjalan/google-apiclient)

## Requirements

* PHP >=8.0

## Installation

Install via composer

```bash
composer require pulkitjalan/google-apiclient
```

## Laravel

To use in laravel add the following to the `providers` array in your `config/app.php`

```php
PulkitJalan\Google\GoogleServiceProvider::class
```

Next add the following to the `aliases` array in your `config/app.php`

```php
'Google' => PulkitJalan\Google\Facades\Google::class
```

Finally run `php artisan vendor:publish --provider="PulkitJalan\Google\GoogleServiceProvider" --tag="config"` to publish the config file.

#### Using an older version of PHP / Laravel?

If you are on a PHP version below 8.0 or a Laravel version below 10.0, use an older version of this package.

## Usage

The `Client` class takes an array as the first parameter, see example of config file below:

```php
return [
    /*
    |----------------------------------------------------------------------------
    | Google application name
    |----------------------------------------------------------------------------
    */
    'application_name' => '',

    /*
    |----------------------------------------------------------------------------
    | Google OAuth 2.0 access
    |----------------------------------------------------------------------------
    |
    | Keys for OAuth 2.0 access, see the API console at
    | https://developers.google.com/console
    |
    */
    'client_id' => '',
    'client_secret' => '',
    'redirect_uri' => '',
    'scopes' => [],
    'access_type' => 'online',
    'prompt' => 'auto',

    /*
    |----------------------------------------------------------------------------
    | Google developer key
    |----------------------------------------------------------------------------
    |
    | Simple API access key, also from the API console. Ensure you get
    | a Server key, and not a Browser key.
    |
    */
    'developer_key' => '',

    /*
    |----------------------------------------------------------------------------
    | Google service account
    |----------------------------------------------------------------------------
    |
    | Set the credentials JSON's location to use assert credentials, otherwise
    | app engine or compute engine will be used.
    |
    */
    'service' =>  [
        /*
        | Enable service account auth or not.
        */
        'enabled' => false,

        /*
        | Path to service account json file
        */
        'file' => '',
    ],

    /*
    |----------------------------------------------------------------------------
    | Additional config for the Google Client
    |----------------------------------------------------------------------------
    |
    | Set any additional config variables supported by the Google Client
    | Details can be found here:
    | https://github.com/google/google-api-php-client/blob/master/src/Google/Client.php
    |
    | NOTE: If client id is specified here, it will get over written by the one above.
    |
    */
    'config' => [],
];

```

To use Google Cloud Platform services, enter the location to the service account JSON file **(not the JSON string itself)**. To use App Engine or Computer Engine, leave it blank.

From [Google's upgrading document](https://github.com/google/google-api-php-client/blob/master/UPGRADING.md):

> Note: P12s are deprecated in favor of service account JSON, which can be generated in the Credentials section of Google Developer Console.


Get `Google\Client`
```php
$client = new PulkitJalan\Google\Client($config);
$googleClient = $client->getClient();
```

Laravel Example:
```php
$googleClient = Google::getClient();
```

Get a service
```php
$client = new PulkitJalan\Google\Client($config);

// returns instance of \Google\Service\Storage
$storage = $client->make('storage');

// list buckets example
$storage->buckets->listBuckets('project id');

// get object example
$storage->objects->get('bucket', 'object');
```

Laravel Example:
```php
// returns instance of \Google\Service\Storage
$storage = Google::make('storage');
// or
$storage = Google::make(\Google\Service\Storage::class);

// list buckets example
$storage->buckets->listBuckets('project id');

// get object example
$storage->objects->get('bucket', 'object');
```

Have a look at [google/google-api-php-client-services](https://github.com/google/google-api-php-client-services) to get a full list of the supported Google Services.
