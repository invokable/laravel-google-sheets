<?php

namespace Tests;

use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Resource\Files;
use Mockery as m;
use Revolution\Google\Client\Facades\Google;
use Revolution\Google\Sheets\Facades\Sheets;

class SheetsDriveTest extends TestCase
{
    /**
     * @var Drive
     */
    protected $service;

    /**
     * @var Files
     */
    protected $files;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = m::mock(Drive::class);
        $this->files = m::mock(Files::class);
        $this->service->files = $this->files;

        Sheets::setDriveService($this->service);
    }

    public function test_list()
    {
        $file = new DriveFile([
            'id' => 'id',
            'name' => 'name',
        ]);

        $files = [
            $file,
        ];

        $this->files->shouldReceive('listFiles->getFiles')->once()->andReturn($files);

        $list = Sheets::spreadsheetList();

        $this->assertSame(['id' => 'name'], $list);
    }

    public function test_null()
    {
        Google::shouldReceive('make')->andReturn($this->service);

        $drive = Sheets::setDriveService(null)->getDriveService();

        $this->assertSame($this->service, $drive);
    }
}
