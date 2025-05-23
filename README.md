# Google Sheets API v4 for Laravel

[![packagist](https://badgen.net/packagist/v/revolution/laravel-google-sheets)](https://packagist.org/packages/revolution/laravel-google-sheets)

## Requirements
- PHP >= 8.2
- Laravel >= 11.0

## Versioning
- Basic : semver
- Drop old PHP or Laravel version : `+0.1`. composer should handle it well.
- Support only latest major version (`master` branch), but you can PR to old branches.

## Installation

### Composer
```
composer require revolution/laravel-google-sheets
```

### Laravel

1. Run `php artisan vendor:publish --tag="google-config"` to publish the google config file

        // config/google.php

        // OAuth
        'client_id'        => env('GOOGLE_CLIENT_ID', ''),
        'client_secret'    => env('GOOGLE_CLIENT_SECRET', ''),
        'redirect_uri'     => env('GOOGLE_REDIRECT', ''),
        'scopes'           => [\Google\Service\Sheets::DRIVE, \Google\Service\Sheets::SPREADSHEETS],
        'access_type'      => 'online',
        'approval_prompt'  => 'auto',
        'prompt'           => 'consent', //"none", "consent", "select_account" default:none

        // or Service Account
        'file'    => storage_path('credentials.json'),
        'enable'  => env('GOOGLE_SERVICE_ENABLED', true),

2. Get API Credentials from https://developers.google.com/console  
Enable `Google Sheets API`, `Google Drive API`.

3. Configure .env as needed

        GOOGLE_APPLICATION_NAME=
        GOOGLE_CLIENT_ID=
        GOOGLE_CLIENT_SECRET=
        GOOGLE_REDIRECT=
        GOOGLE_DEVELOPER_KEY=
        GOOGLE_SERVICE_ENABLED=
        GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION=

## Demo
- https://github.com/kawax/google-sheets-project
- https://sheets.kawax.biz/

Another Google API Series.
- https://github.com/kawax/laravel-google-photos
- https://github.com/kawax/laravel-google-searchconsole

## Select auth type
You must select an authentication type and configure it appropriately.

- Service Account : Access to only your own spreadsheets.
- OAuth : Access to user's spreadsheets.
- API key: Access to public spreadsheets.

## Usage

| id  | name  | mail  |
|-----|-------|-------|
| 1   | name1 | mail1 |
| 2   | name2 | mail2 |

https://docs.google.com/spreadsheets/d/{spreadsheetID}/...

### Basic Laravel Usage
```php
use Revolution\Google\Sheets\Facades\Sheets;

$user = $request->user();

$token = [
      'access_token'  => $user->access_token,
      'refresh_token' => $user->refresh_token,
      'expires_in'    => $user->expires_in,
      'created'       => $user->updated_at->getTimestamp(),
];

// all() returns array
$values = Sheets::setAccessToken($token)->spreadsheet('spreadsheetId')->sheet('Sheet 1')->all();
// [
//   ['id', 'name', 'mail'],
//   ['1', 'name1', 'mail1'],
//   ['2', 'name1', 'mail2']
// ]
```

### Basic Non-Laravel Usage

```php
use Google\Client;
use Revolution\Google\Sheets\SheetsClient;

$client = new Client();
$client->setScopes([Google\Service\Sheets::DRIVE, Google\Service\Sheets::SPREADSHEETS]);
// setup Google Client
// ...

$service = new \Google\Service\Sheets($client);

$sheets = new SheetsClient();
$sheets->setService($service);

$values = $sheets->spreadsheet('spreadsheetID')->sheet('Sheet 1')->all();
```

### Get a sheet's values with the header as the key
```php
use Revolution\Google\Sheets\Facades\Sheets;

// get() returns Laravel Collection
$rows = Sheets::sheet('Sheet 1')->get();

$header = $rows->pull(0);
$values = Sheets::collection(header: $header, rows: $rows);
$values->toArray()
// [
//   ['id' => '1', 'name' => 'name1', 'mail' => 'mail1'],
//   ['id' => '2', 'name' => 'name2', 'mail' => 'mail2']
// ]
```

Blade
```php
@foreach($values as $value)
  {{ data_get($value, 'name') }}
@endforeach
```

### Using A1 Notation
```php
use Revolution\Google\Sheets\Facades\Sheets;

$values = Sheets::sheet('Sheet 1')->range('A1:B2')->all();
// [
//   ['id', 'name'],
//   ['1', 'name1'],
// ]
```

### Updating a specific range
```php
use Revolution\Google\Sheets\Facades\Sheets;

Sheets::sheet('Sheet 1')->range('A4')->update([['3', 'name3', 'mail3']]);
$values = Sheets::range('')->all();
// [
//   ['id', 'name', 'mail'],
//   ['1', 'name1', 'mail1'],
//   ['2', 'name1', 'mail2'],
//   ['3', 'name3', 'mail3']
// ]
```

### Append a set of values to a sheet
```php
use Revolution\Google\Sheets\Facades\Sheets;

// When we don't provide a specific range, the sheet becomes the default range
Sheets::sheet('Sheet 1')->append([['3', 'name3', 'mail3']]);
$values = Sheets::all();
// [
//   ['id', 'name', 'mail'],
//   ['1', 'name1', 'mail1'],
//   ['2', 'name1', 'mail2'],
//   ['3', 'name3', 'mail3']
// ]
```

### Append a set of values with keys
```php
use Revolution\Google\Sheets\Facades\Sheets;

// When providing an associative array, values get matched up to the headers in the provided sheet
Sheets::sheet('Sheet 1')->append([['name' => 'name4', 'mail' => 'mail4', 'id' => 4]]);
$values = Sheets::all();
// [
//   ['id', 'name', 'mail'],
//   ['1', 'name1', 'mail1'],
//   ['2', 'name1', 'mail2'],
//   ['3', 'name3', 'mail3'],
//   ['4', 'name4', 'mail4'],
// ]
```

### Add a new sheet
```php
use Revolution\Google\Sheets\Facades\Sheets;

Sheets::spreadsheetByTitle($title)->addSheet('New Sheet Title');
```

### Deleting a sheet
```php
use Revolution\Google\Sheets\Facades\Sheets;

Sheets::spreadsheetByTitle($title)->deleteSheet('Old Sheet Title');
```

### Specifying query parameters
```php
use Revolution\Google\Sheets\Facades\Sheets;

$values = Sheets::sheet('Sheet 1')->majorDimension('DIMENSION_UNSPECIFIED')
                                  ->valueRenderOption('FORMATTED_VALUE')
                                  ->dateTimeRenderOption('SERIAL_NUMBER')
                                  ->all();
```
https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets.values/get#query-parameters

## Use original Google_Service_Sheets
```php
use Revolution\Google\Sheets\Facades\Sheets;

$sheets->spreadsheets->...
$sheets->spreadsheets_sheets->...
$sheets->spreadsheets_values->...

Sheets::getService()->spreadsheets->...

```
see https://github.com/google/google-api-php-client-services/blob/master/src/Google/Service/Sheets.php

## LICENSE
MIT  
Copyright kawax
