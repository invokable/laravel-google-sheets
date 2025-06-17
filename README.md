# Google Sheets API v4 for Laravel

[![packagist](https://badgen.net/packagist/v/revolution/laravel-google-sheets)](https://packagist.org/packages/revolution/laravel-google-sheets)
[![Maintainability](https://qlty.sh/badges/483a3481-fbc3-4e43-a83f-a843a25a45d8/maintainability.svg)](https://qlty.sh/gh/invokable/projects/laravel-google-sheets)
[![Code Coverage](https://qlty.sh/badges/483a3481-fbc3-4e43-a83f-a843a25a45d8/test_coverage.svg)](https://qlty.sh/gh/invokable/projects/laravel-google-sheets)
[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/invokable/laravel-google-sheets)

## Overview

...

### Concept

The main purpose of this package is **reading from Google Sheets**. Instead of specifying detailed conditions before reading, it is assumed that you first retrieve all data as a Laravel Collection and then process the data on the Laravel side.

## Requirements
- PHP >= 8.2
- Laravel >= 11.0

## Installation

### Composer
```bash
composer require revolution/laravel-google-sheets
```

### Laravel Configuration

1. Run `php artisan vendor:publish --tag="google-config"` to publish the google config file

2. Enable Google APIs in [Google Cloud Console](https://console.cloud.google.com/):
   - **Google Sheets API**
   - **Google Drive API**

3. Choose your authentication method and configure accordingly (see [Authentication](#authentication) section below)

## Demo & Examples

- **Working Demo**: [Laravel Google Sheets Demo](https://sheets.kawax.biz/)
- **Complete Example Project**: [google-sheets-project](https://github.com/kawax/google-sheets-project)

**Related Google API Packages:**
- [Laravel Google Photos](https://github.com/invokable/laravel-google-photos)
- [Laravel Google Search Console](https://github.com/invokable/laravel-google-searchconsole)

## Authentication

You must choose an authentication method based on your use case. This package supports three authentication methods:

> **Scopes setting is required in config/google.php for both OAuth and Service Account authentication.**
> Example:
> ```php
> 'scopes' => [
>     \Google\Service\Sheets::SPREADSHEETS,
>     \Google\Service\Drive::DRIVE,
> ],
> ```

### Authentication Methods Comparison

| Method              | Use Case                            | User Interaction      | Access Scope                        | Complexity |
|---------------------|-------------------------------------|-----------------------|-------------------------------------|------------|
| **Service Account** | Server-to-server, automated systems | None required         | Specific spreadsheets you own/share | Medium     |
| **OAuth 2.0**       | User-facing applications            | User consent required | User's own spreadsheets             | High       |
| **API Key**         | Public data only                    | None required         | Public spreadsheets only            | Low        |

### Service Account (Recommended for most applications)
**Best for:** Background jobs, automated systems, server-to-server access

Access spreadsheets that your application owns or has been granted access to. No user interaction required.

```env
GOOGLE_SERVICE_ENABLED=true
GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION=storage/app/google-service-account.json
```

**📖 [Complete Service Account Setup Guide →](docs/service-account.md)**

### OAuth 2.0
**Best for:** Applications where users access their own Google Sheets

Users grant permission to access their personal Google Sheets. Requires user consent flow.

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT=https://your-app.com/auth/callback
```

**📖 [Complete OAuth Setup Guide →](docs/oauth.md)**

### API Key (Public Access Only)
**Best for:** Accessing publicly shared, read-only spreadsheets

Limited to reading data from spreadsheets that are publicly accessible. No authentication flow required.

```env
GOOGLE_DEVELOPER_KEY=your-api-key
```

To use API Key authentication:
1. Get an API Key from [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Ensure your spreadsheet is publicly accessible (shared with "Anyone with the link")
3. Use the key in your application:

```php
use Revolution\Google\Sheets\Facades\Sheets;

// API key is automatically used when configured
$values = Sheets::spreadsheet('public-spreadsheet-id')->sheet('Sheet1')->all();
```

**⚠️ API Key Limitations:**
- Read-only access
- Only works with publicly shared spreadsheets
- No write operations (update, append, delete)
- No access to private spreadsheets

## Quick Start

Here's how to get started quickly with each authentication method:

### Using Service Account (Recommended)

1. **Setup**: Follow the [Service Account Setup Guide](docs/service-account.md)
2. **Configure**: Add to your `.env` file:
   ```env
   GOOGLE_SERVICE_ENABLED=true
   GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION=storage/app/google-service-account.json
   ```
3. **Share**: Share your Google Sheet with the service account email
4. **Use**: Start reading/writing data:
   ```php
   use Revolution\Google\Sheets\Facades\Sheets;
   
   $values = Sheets::spreadsheet('your-spreadsheet-id')
       ->sheet('Sheet1')
       ->all();
   ```

### Using OAuth 2.0

1. **Setup**: Follow the [OAuth Setup Guide](docs/oauth.md)
2. **Configure**: Add OAuth credentials to your `.env` file
3. **Authenticate**: Handle user authentication flow
4. **Use**: Access user's spreadsheets:
   ```php
   use Revolution\Google\Sheets\Facades\Sheets;
   
   $token = ['access_token' => $user->access_token, ...];
   $values = Sheets::setAccessToken($token)
       ->spreadsheet('user-spreadsheet-id')
       ->sheet('Sheet1')
       ->all();
   ```

### Using API Key (Public Access)

1. **Setup**: Get API key from Google Cloud Console
2. **Configure**: Add to your `.env` file:
   ```env
   GOOGLE_DEVELOPER_KEY=your-api-key
   ```
3. **Use**: Read public spreadsheets:
   ```php
   use Revolution\Google\Sheets\Facades\Sheets;
   
   // Works only with publicly shared spreadsheets
   $values = Sheets::spreadsheet('public-spreadsheet-id')
       ->sheet('Sheet1')
       ->all();
   ```

## Usage

Consider this example spreadsheet structure:

| id  | name  | mail  |
|-----|-------|-------|
| 1   | name1 | mail1 |
| 2   | name2 | mail2 |

Spreadsheet URL: `https://docs.google.com/spreadsheets/d/{spreadsheetID}/...`

### Service Account Usage

When using Service Account authentication, no token setup is required:

```php
use Revolution\Google\Sheets\Facades\Sheets;

// Service account authentication is automatic when configured
$values = Sheets::spreadsheet('spreadsheetId')->sheet('Sheet 1')->all();
// [
//   ['id', 'name', 'mail'],
//   ['1', 'name1', 'mail1'],
//   ['2', 'name2', 'mail2']
// ]
```

### OAuth Usage

When using OAuth authentication, you need to set the user's access token:

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

## FAQ

### Which authentication method should I use?

- **Service Account**: Best for most Laravel applications, automated systems, and background jobs
- **OAuth 2.0**: Use when users need to access their own Google Sheets
- **API Key**: Only for reading public spreadsheets (very limited use cases)

### How do I share a spreadsheet with my Service Account?

1. Open your Google Sheet
2. Click the "Share" button
3. Find the `client_email` in your service account JSON file
4. Share the spreadsheet with this email address
5. Grant "Editor" permissions for read/write access

### Can I access multiple spreadsheets?

Yes! You can access any spreadsheet that:
- Is shared with your service account (Service Account method)
- The authenticated user has access to (OAuth method)
- Is publicly accessible (API Key method)

### How do I handle authentication errors?

Common solutions:
- **Service Account**: Ensure the spreadsheet is shared with the service account email
- **OAuth**: Check if the access token is expired and refresh it
- **API Key**: Verify the spreadsheet is publicly accessible

### Can I use this package without Laravel?

Yes! See the "Basic Non-Laravel Usage" example in the Usage section above.

### How do I deploy this to production?

- Store service account credentials securely (outside web root)
- Use environment variables for all configuration
- Never commit credential files to version control
- Consider using different service accounts for different environments

## LICENSE
MIT  
Copyright kawax
