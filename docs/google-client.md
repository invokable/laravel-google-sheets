# Use original Google api client

This package includes **[pulkitjalan/google-apiclient](https://github.com/pulkitjalan/google-apiclient)** to support the latest Laravel.

If you want to use the original package, you can change it with AppServiceProvider.

## Install package
```shell
composer require pulkitjalan/google-apiclient
```

## AppServiceProvider

```php
use PulkitJalan\Google\Client as GoogleClient;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->alias(GoogleClient::class, 'google-client');
    }
}
```
