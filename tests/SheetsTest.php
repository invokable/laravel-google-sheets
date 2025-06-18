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
        $this->google->expects('make')->once()->andReturns(m::mock(\Google\Service\Sheets::class));

        //        Sheets::setService($this->google->make('Sheets'));

        $this->assertInstanceOf(\Google\Service\Sheets::class, Sheets::getService());
    }

    public function test_set_access_token()
    {
        $this->google->expects('getCache->clear')->once();
        $this->google->expects('setAccessToken')->once();
        $this->google->expects('isAccessTokenExpired')->once()->andReturns(true);
        $this->google->expects('fetchAccessTokenWithRefreshToken')->once();
        $this->google->expects('make')->times(2)->andReturns(
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
        Sheets::expects('setAccessToken')->with('test')->once()->andReturn(m::self());

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
