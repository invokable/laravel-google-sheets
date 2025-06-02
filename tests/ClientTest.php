<?php

namespace Tests;

use Mockery as m;
use PHPUnit\Framework\Attributes\RequiresMethod;
use PulkitJalan\Google\Client as GoogleClient;
use Revolution\Google\Client\Exceptions\UnknownServiceException;
use Revolution\Google\Client\Facades\Google;
use Revolution\Google\Client\GoogleApiClient;

class ClientTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    public function test_client_getter()
    {
        $client = m::mock(GoogleApiClient::class, [[]])->makePartial();

        $this->assertInstanceOf('Google\Client', $client->getClient());
    }

    public function test_client_getter_with_additional_config()
    {
        $client = m::mock(GoogleApiClient::class, [[
            'config' => [
                'subject' => 'test',
            ],
        ]])->makePartial();

        $this->assertEquals('test', $client->getClient()->getConfig('subject'));
    }

    public function test_service_make()
    {
        $client = m::mock(GoogleApiClient::class, [[]])->makePartial();

        $this->assertInstanceOf('Google\Service\Storage', $client->make('storage'));
    }

    public function test_service_make_exception()
    {
        $client = m::mock(GoogleApiClient::class, [[]])->makePartial();

        $this->expectException(UnknownServiceException::class);

        $client->make('storag');
    }

    public function test_magic_method_exception()
    {
        $client = new GoogleApiClient([]);

        $this->expectException('BadMethodCallException');

        $client->getAuthTest();
    }

    public function test_no_credentials()
    {
        $client = new GoogleApiClient([]);

        $this->assertFalse($client->isUsingApplicationDefaultCredentials());
    }

    public function test_default_credentials()
    {
        $client = new GoogleApiClient([
            'service' => [
                'enable' => true,
                'file' => __DIR__.'/data/test.json',
            ],
        ]);

        $this->assertTrue($client->isUsingApplicationDefaultCredentials());
    }

    #[RequiresMethod(GoogleClient::class, 'make')]
    public function test_original_client()
    {
        $this->assertInstanceOf(GoogleApiClient::class, Google::getFacadeRoot());
        Google::clearResolvedInstances();

        $this->app->alias(GoogleClient::class, 'google-client');

        $this->assertInstanceOf(GoogleClient::class, app('google-client'));
        $this->assertInstanceOf(GoogleClient::class, Google::getFacadeRoot());
    }
}
