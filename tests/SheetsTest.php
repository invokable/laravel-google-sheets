<?php

namespace Tests;

use Mockery as m;
use Revolution\Google\Client\GoogleApiClient;
use Revolution\Google\Sheets\Facades\Sheets;
use Revolution\Google\Sheets\Traits\GoogleSheets;

class SheetsTest extends TestCase
{
    /**
     * @var GoogleApiClient
     */
    protected $google;

    protected function setUp(): void
    {
        parent::setUp();

        $this->google = m::mock(GoogleApiClient::class);
        app()->instance(GoogleApiClient::class, $this->google);
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    public function test_service()
    {
        $this->google->shouldReceive('make')->once()->andReturns(m::mock(\Google\Service\Sheets::class));

        //        Sheets::setService($this->google->make('Sheets'));

        $this->assertInstanceOf(\Google\Service\Sheets::class, Sheets::getService());
    }

    public function test_set_access_token()
    {
        $this->google->shouldReceive('getCache')->once()->andReturn(m::self());
        $this->google->shouldReceive('clear')->once();
        $this->google->shouldReceive('setAccessToken')->once();
        $this->google->shouldReceive('isAccessTokenExpired')->once()->andReturns(true);
        $this->google->shouldReceive('fetchAccessTokenWithRefreshToken')->once();
        $this->google->shouldReceive('make')->times(2)->andReturns(
            m::mock(\Google\Service\Sheets::class),
            m::mock(\Google\Service\Drive::class)
        );

        $photos = Sheets::setAccessToken([
            'access_token' => 'test',
            'refresh_token' => 'test',
            'expires_in' => 0,
        ]);

        $this->assertInstanceOf(\Google\Service\Sheets::class, $photos->getService());
    }

    public function test_trait()
    {
        Sheets::shouldReceive('setAccessToken')->with('test')->once()->andReturn(m::self());

        $sheets = (new User)->sheets();

        $this->assertNotNull($sheets);
    }
}

class User
{
    use GoogleSheets;

    public function sheetsAccessToken()
    {
        return 'test';
    }
}
