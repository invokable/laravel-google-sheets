<?php

namespace Revolution\Google\Sheets\Facades;

use Google\Service\Drive;
use Google\Service\Sheets\AppendValuesResponse;
use Google\Service\Sheets as GoogleSheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetResponse;
use Google\Service\Sheets\BatchUpdateValuesResponse;
use Google\Service\Sheets\ClearValuesResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Revolution\Google\Sheets\Contracts\Factory;

/**
 * @method static self setAccessToken(array|string $token)
 * @method static self spreadsheet(string $spreadsheetId)
 * @method static self spreadsheetByTitle(string $title)
 * @method static self sheet(string $sheet)
 * @method static self sheetById(string $sheetId)
 * @method static array sheetList()
 * @method static BatchUpdateSpreadsheetResponse addSheet(string $sheetTitle)
 * @method static BatchUpdateSpreadsheetResponse deleteSheet(string $sheetTitle)
 * @method static Collection get()
 * @method static Collection collection(array $header, array|Collection $rows)
 * @method static array all()
 * @method static array first()
 * @method static BatchUpdateValuesResponse update(array $value, string $valueInputOption = 'RAW')
 * @method static ClearValuesResponse|null clear()
 * @method static AppendValuesResponse append(array $values, string $valueInputOption = 'RAW', string $insertDataOption = 'OVERWRITE')
 * @method static self range(string $range)
 * @method static self majorDimension(string $majorDimension)
 * @method static self valueRenderOption(string $valueRenderOption)
 * @method static self dateTimeRenderOption(string $dateTimeRenderOption)
 * @method static GoogleSheets getService()
 * @method static self setService(mixed $service)
 * @method static Drive getDriveService()
 * @method static self setDriveService(mixed $drive)
 * @method static array spreadsheetList()
 * @method static object spreadsheetProperties()
 * @method static object sheetProperties()
 * @method static string getSpreadsheetId()
 * @method static void macro(string $name, object|callable $macro)
 *
 * @see \Revolution\Google\Sheets\SheetsClient
 */
class Sheets extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Factory::class;
    }
}
