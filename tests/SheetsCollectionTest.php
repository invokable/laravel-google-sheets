<?php

namespace Tests;

use Revolution\Google\Sheets\Facades\Sheets;

class SheetsCollectionTest extends TestCase
{
    public function test_collection()
    {
        $header = ['id', 'name', 'mail'];
        $rows = [
            ['1', 'name1', 'mail1'],
            ['2', 'name2', 'mail2'],
        ];

        $collection = Sheets::collection($header, $rows);

        $this->assertEquals('name1', $collection->first()['name']);
    }

    public function test_collection2()
    {
        $header = ['id', 'name', 'mail'];
        $rows = [
            ['1', 'name1', 'mail1'],
            ['2', 'name2'],
        ];

        $collection = Sheets::collection($header, $rows);

        $this->assertNotNull($collection->last()['mail']);
    }

    public function test_collection3()
    {
        $rows = collect([
            ['id', 'name', 'mail'],
            ['1', 'name1', 'mail1'],
            ['2', 'name2', 'mail3'],
        ]);

        $header = $rows->pull(0);

        $collection = Sheets::collection($header, $rows);

        $this->assertEquals('mail3', $collection->last()['mail']);
    }
}
