<?php

namespace Revolution\Google\Sheets;

use BadMethodCallException;
use Google\Service\Sheets as GoogleSheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\BatchUpdateSpreadsheetResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use Revolution\Google\Client\Facades\Google;
use Revolution\Google\Sheets\Contracts\Factory;

class SheetsClient implements Factory
{
    use Concerns\SheetsCollection;
    use Concerns\SheetsDrive;
    use Concerns\SheetsProperties;
    use Concerns\SheetsValues;
    use Conditionable;
    use Macroable {
        __call as macroCall;
    }

    protected ?GoogleSheets $service = null;

    protected ?string $spreadsheetId = null;

    protected ?string $sheet = null;

    public function setService(mixed $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getService(): GoogleSheets
    {
        return $this->service ??= Google::make('sheets');
    }

    /**
     * set access_token and set new service.
     */
    public function setAccessToken(#[\SensitiveParameter] array|string $token): static
    {
        Google::getCache()->clear();

        Google::setAccessToken($token);

        if (isset($token['refresh_token']) && Google::isAccessTokenExpired()) {
            Google::fetchAccessTokenWithRefreshToken();
        }

        return $this->setService(Google::make('sheets'))
            ->setDriveService(Google::make('drive'));
    }

    public function getAccessToken(): ?array
    {
        return $this->getService()->getClient()->getAccessToken();
    }

    public function spreadsheet(string $spreadsheetId): static
    {
        $this->spreadsheetId = $spreadsheetId;

        return $this;
    }

    public function spreadsheetByTitle(string $title): static
    {
        $list = $this->spreadsheetList();

        $this->spreadsheetId = Arr::get(array_flip($list), $title);

        return $this;
    }

    public function sheet(string $sheet): static
    {
        $this->sheet = $sheet;

        return $this;
    }

    public function sheetById(string $sheetId): static
    {
        $list = $this->sheetList();

        $this->sheet = Arr::get($list, $sheetId);

        return $this;
    }

    public function sheetList(): array
    {
        $list = [];

        $sheets = $this->getService()->spreadsheets->get($this->getSpreadsheetId())->getSheets();

        foreach ($sheets as $sheet) {
            $list[$sheet->getProperties()->getSheetId()] = $sheet->getProperties()->getTitle();
        }

        return $list;
    }

    public function addSheet(string $sheetTitle): BatchUpdateSpreadsheetResponse
    {
        $body = new BatchUpdateSpreadsheetRequest(
            [
                'requests' => [
                    'addSheet' => [
                        'properties' => [
                            'title' => $sheetTitle,
                        ],
                    ],
                ],
            ],
        );

        return $this->getService()->spreadsheets->batchUpdate($this->getSpreadsheetId(), $body);
    }

    public function deleteSheet(string $sheetTitle): BatchUpdateSpreadsheetResponse
    {
        $list = $this->sheetList();
        $id = Arr::get(array_flip($list), $sheetTitle);

        $body = new BatchUpdateSpreadsheetRequest(
            [
                'requests' => [
                    'deleteSheet' => [
                        'sheetId' => $id,
                    ],
                ],
            ],
        );

        return $this->getService()->spreadsheets->batchUpdate($this->getSpreadsheetId(), $body);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __get(string $property)
    {
        if (property_exists($this->getService(), $property)) {
            return $this->getService()->{$property};
        }

        throw new InvalidArgumentException(sprintf('Property [%s] does not exist.', $property));
    }

    /**
     * Magic call method.
     *
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->getService(), $method)) {
            return $this->getService()->{$method}(...array_values($parameters));
        }

        return $this->macroCall($method, $parameters);
    }
}
