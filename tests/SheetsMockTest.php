<?php

namespace Tests;

use Google\Service\Sheets\AppendValuesResponse;
use Google\Service\Sheets as GoogleSheets;
use Google\Service\Sheets\BatchGetValuesResponse;
use Google\Service\Sheets\BatchUpdateSpreadsheetResponse;
use Google\Service\Sheets\BatchUpdateValuesResponse;
use Google\Service\Sheets\Resource\Spreadsheets;
use Google\Service\Sheets\Resource\SpreadsheetsValues;
use Google\Service\Sheets\Sheet;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\UpdateValuesResponse;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Collection;
use Mockery as m;
use Revolution\Google\Sheets\SheetsClient;

class SheetsMockTest extends TestCase
{
    protected SheetsClient $sheet;

    protected GoogleSheets $service;

    protected Spreadsheets $spreadsheets;

    protected SpreadsheetsValues $values;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = m::mock(GoogleSheets::class)->makePartial();
        $this->spreadsheets = m::mock(Spreadsheets::class);
        $this->service->spreadsheets = $this->spreadsheets;
        $this->values = m::mock(SpreadsheetsValues::class);
        $this->service->spreadsheets_values = $this->values;

        $this->sheet = new SheetsClient;

        $this->sheet->setService($this->service);
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    public function test_sheets_all()
    {
        $response = new BatchGetValuesResponse;
        $valueRange = new ValueRange;
        $valueRange->setValues([['test1' => '1'], ['test2' => '2']]);
        $response->setValueRanges([$valueRange]);

        $this->values->shouldReceive('batchGet')->with(m::any(), m::any())->once()->andReturn($response);

        $values = $this->sheet->spreadsheet('test')
            ->sheet('test')
            ->majorDimension('test')
            ->valueRenderOption('test')
            ->dateTimeRenderOption('test')
            ->all();

        $this->assertGreaterThan(1, count($values));
        $this->assertSame([['test1' => '1'], ['test2' => '2']], $values);
    }

    public function test_sheets_empty()
    {
        $response = new BatchGetValuesResponse;
        $valueRange = new ValueRange;
        $valueRange->setValues(null);
        $response->setValueRanges([$valueRange]);

        $this->values->shouldReceive('batchGet')->with(m::any(), m::any())->once()->andReturn($response);

        $values = $this->sheet->all();

        $this->assertSame([], $values);
    }

    public function test_sheets_get()
    {
        $response = new BatchGetValuesResponse;
        $valueRange = new ValueRange;
        $valueRange->setValues([['test1' => '1'], ['test2' => '2']]);
        $response->setValueRanges([$valueRange]);

        $this->values->shouldReceive('batchGet')->with(m::any(), m::any())->once()->andReturn($response);

        $values = $this->sheet->get();

        $this->assertInstanceOf(Collection::class, $values);
        $this->assertSame([['test1' => '1'], ['test2' => '2']], $values->toArray());
    }

    public function test_sheets_update()
    {
        $response = new BatchUpdateValuesResponse;

        $this->values->shouldReceive('batchUpdate')->once()->andReturn($response);

        $values = $this->sheet->sheet('test')->range('A1')->update([['test']]);

        $this->assertEquals('test!A1', $this->sheet->ranges());
        $this->assertInstanceOf(BatchUpdateValuesResponse::class, $values);
    }

    public function test_sheets_first()
    {
        $response = new BatchGetValuesResponse;
        $valueRange = new ValueRange;
        $valueRange->setValues([['test1' => '1'], ['test2' => '2']]);
        $response->setValueRanges([$valueRange]);

        $this->values->shouldReceive('batchGet')->with(m::any(), m::any())->once()->andReturn($response);

        $value = $this->sheet->first();

        $this->assertSame(['test1' => '1'], $value);
    }

    public function test_sheets_first_empty()
    {
        $response = new BatchGetValuesResponse;
        $valueRange = new ValueRange;
        $valueRange->setValues(null);
        $response->setValueRanges([$valueRange]);

        $this->values->shouldReceive('batchGet')->with(m::any(), m::any())->once()->andReturn($response);

        $value = $this->sheet->first();

        $this->assertSame([], $value);
    }

    public function test_sheets_clear()
    {
        $this->values->shouldReceive('clear')->once();

        $value = $this->sheet->clear();

        $this->assertNull($value);
    }

    public function test_sheets_append()
    {
        $response = new AppendValuesResponse;
        $updates = new UpdateValuesResponse;
        $valueRange = new ValueRange;
        $valueRange->setValues([['test1' => '1'], ['test2' => '2']]);
        $response->setUpdates($updates);

        $this->values->shouldReceive('append')->once()->andReturn($response);

        $value = $this->sheet->append([[]]);

        $this->assertSame($response, $value);
    }

    public function test_sheets_append_with_keys()
    {
        $response = new BatchGetValuesResponse;
        $valueRange = new ValueRange;
        $valueRange->setValues([['header1', 'header2'], ['value1', 'value2']]);
        $response->setValueRanges([$valueRange]);

        $this->values->shouldReceive('batchGet')
            ->with(m::any(), m::any())
            ->andReturn($response);

        $ordered = $this->sheet->orderAppendables([['header2' => 'value3', 'header1' => null]]);
        $this->assertSame([['', 'value3']], $ordered);
    }

    public function test_spreadsheet_properties()
    {
        $this->spreadsheets->shouldReceive('get->getProperties->toSimpleObject')->once()->andReturn(new \stdClass);

        $properties = $this->sheet->spreadsheetProperties();

        $this->assertInstanceOf(\stdClass::class, $properties);
    }

    public function test_sheet_properties()
    {
        $sheet = m::mock(Spreadsheet::class);
        $sheet->shouldReceive('getProperties->toSimpleObject')->once()->andReturn(new \stdClass);

        $this->spreadsheets->shouldReceive('get->getSheets')->once()->andReturn([$sheet]);

        $properties = $this->sheet->sheetProperties();

        $this->assertInstanceOf(\stdClass::class, $properties);
    }

    public function test_magic_get()
    {
        $spreadsheets = $this->sheet->spreadsheets;

        $this->assertNotNull($spreadsheets);
    }

    public function test_sheets_list()
    {
        $sheets = new Sheet([
            'properties' => [
                'sheetId' => 'sheetId',
                'title' => 'title',
            ],
        ]);

        $this->spreadsheets->shouldReceive('get->getSheets')->andReturn([$sheets]);
        $values = $this->sheet->sheetList();

        $this->assertSame(['sheetId' => 'title'], $values);
    }

    public function test_sheet_by_id()
    {
        $sheets = new Sheet([
            'properties' => [
                'sheetId' => 'sheetId',
                'title' => 'title',
            ],
        ]);

        $sheet = m::mock(SheetsClient::class)->makePartial();

        $sheet->shouldReceive('sheetList')->andReturn([$sheets]);

        $sheet->sheetById('sheetId');

        $this->assertNotNull($sheet);
    }

    public function test_spreadsheet_by_title()
    {
        $list = [
            'id' => 'title',
        ];

        $sheet = m::mock(SheetsClient::class)->makePartial();

        $sheet->shouldReceive('spreadsheetList')->andReturn($list);

        $sheet->spreadsheetByTitle('title');

        $this->assertNotNull($sheet);
    }

    public function test_get_access_token()
    {
        $sheet = m::mock(SheetsClient::class)->makePartial();

        $token = $sheet->getAccessToken();

        $this->assertNull($token);
    }

    public function test_property()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->sheet->test;
    }

    public function test_get_client()
    {
        $client = $this->sheet->getClient();

        $this->assertNull($client);
    }

    public function test_add_sheet()
    {
        $this->spreadsheets
            ->shouldReceive('batchUpdate')
            ->andReturn(new BatchUpdateSpreadsheetResponse);

        $response = $this->sheet->addSheet('new sheet');
        $this->assertNotNull($response);
    }

    public function test_delete_sheet()
    {
        $sheets = new Sheet([
            'properties' => [
                'sheetId' => 'sheetId',
                'title' => 'title',
            ],
        ]);

        $this->spreadsheets->shouldReceive('get->getSheets')->andReturn([$sheets]);
        $this->spreadsheets
            ->shouldReceive('batchUpdate')
            ->andReturn(new BatchUpdateSpreadsheetResponse);

        $this->sheet->shouldReceive('sheetList')->andReturn([$sheets]);
        $response = $this->sheet->deleteSheet('title');
        $this->assertNotNull($response);
    }

    public function test_get_proper_ranges()
    {
        $this->values
            ->shouldReceive('batchUpdate')
            ->times(3)
            ->andReturn(new BatchUpdateValuesResponse);

        // If no range is provided, we get the sheet automatically
        $this->sheet->sheet('test')->update([['test']]);
        $this->assertEquals('test', $this->sheet->ranges());

        // If we provide the full range, it returns accurately
        $this->sheet->sheet('test')->range('test!A1')->update([['test']]);
        $this->assertEquals('test!A1', $this->sheet->ranges());

        // If we only provide part of the range, we get the full proper range
        $this->sheet->sheet('test')->range('A1')->update([['test']]);
        $this->assertEquals('test!A1', $this->sheet->ranges());
    }
}
