<?php

namespace Tests;

use Illuminate\Support\Collection;
use PulkitJalan\Google\GoogleServiceProvider as PulkitJalanServiceProvider;
use Revolution\Google\Client\Providers\GoogleServiceProvider;
use Revolution\Google\Sheets\Providers\SheetsServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return collect([
            SheetsServiceProvider::class,
            GoogleServiceProvider::class,
        ])->when(class_exists(PulkitJalanServiceProvider::class), function (Collection $collection) {
            $collection->add(PulkitJalanServiceProvider::class);
        })->toArray();
    }

    protected function getPackageAliases($app): array
    {
        return [];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        //
    }
}
