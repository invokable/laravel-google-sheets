<?php

namespace Revolution\Google\Sheets\Contracts;

use Google\Service\Sheets;
use Google\Service\Sheets\AppendValuesResponse;
use Google\Service\Sheets\BatchUpdateSpreadsheetResponse;
use Google\Service\Sheets\BatchUpdateValuesResponse;
use Google\Service\Sheets\ClearValuesResponse;
use Illuminate\Support\Collection;

interface Factory
{
    public function setService(mixed $service): self;

    public function getService(): Sheets;

    /**
     * set access_token and set new service.
     */
    public function setAccessToken(array|string $token): self;

    public function spreadsheet(string $spreadsheetId): self;

    public function spreadsheetByTitle(string $title): self;

    public function sheet(string $sheet): self;

    public function sheetById(string $sheetId): self;

    public function sheetList(): array;

    public function get(): Collection;

    public function collection(array $header, array|Collection $rows): Collection;

    public function spreadsheetList(): array;

    public function setDriveService(mixed $drive): self;

    public function getDriveService(): mixed;

    public function spreadsheetProperties(): object;

    public function sheetProperties(): object;

    public function all(): array;

    public function first(): array;

    public function update(array $value, string $valueInputOption = 'RAW'): BatchUpdateValuesResponse;

    public function clear(): ?ClearValuesResponse;

    public function append(
        array $value,
        string $valueInputOption = 'RAW',
        string $insertDataOption = 'OVERWRITE',
    ): AppendValuesResponse;

    public function ranges(): ?string;

    public function range(string $range): self;

    public function majorDimension(string $majorDimension): self;

    public function dateTimeRenderOption(string $dateTimeRenderOption): self;

    public function getSpreadsheetId(): string;

    public function addSheet(string $sheetTitle): BatchUpdateSpreadsheetResponse;

    public function deleteSheet(string $sheetTitle): BatchUpdateSpreadsheetResponse;
}
