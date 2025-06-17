# Onboarding Guide: Laravel Google Sheets Integration Package

## Overview

**revolution/laravel-google-sheets** is a Laravel package that provides a simplified, Laravel-idiomatic interface for interacting with Google Sheets API v4. The package abstracts the complexities of the underlying Google API PHP client library, offering developers a streamlined way to perform common spreadsheet operations within Laravel applications.

**Target Users**: Laravel developers who need to integrate Google Sheets functionality into their web applications.

**Key Capabilities**:
- **Data Operations**: Read, write, update, and clear spreadsheet data with fluent API methods
- **Sheet Management**: Create, delete, and list sheets within spreadsheets
- **Authentication Flexibility**: Supports OAuth 2.0 (user-specific), Service Accounts (server-to-server), and API keys (public data)
- **Laravel Integration**: Provides facades, traits for Eloquent models, and service providers for seamless framework integration
- **Data Transformation**: Converts raw Google API responses into Laravel Collections for easy manipulation
- **Extensibility**: Macro system allows custom method addition to the main facade

**Primary Use Cases**:
- User dashboards that display Google Sheets data
- Data import/export functionality
- Automated reporting systems
- Multi-user applications where each user manages their own spreadsheets

## Project Organization

### Core Systems Architecture

The package is organized around several interconnected systems:

1. **Core Interface Layer** (`src/`)
    - `SheetsClient.php` - Main implementation class that handles Google Sheets operations
    - `Facades/Sheets.php` - Laravel facade providing static access to SheetsClient
    - `Contracts/Factory.php` - Interface defining the core contract for Google Sheets operations

2. **Modular Functionality** (`src/Concerns/`)
    - `SheetsValues.php` - CRUD operations (create, read, update, delete) for spreadsheet data
    - `SheetsCollection.php` - Data transformation utilities for converting API responses to Laravel Collections
    - `SheetsProperties.php` - Methods for retrieving spreadsheet and sheet metadata
    - `SheetsDrive.php` - Google Drive integration for spreadsheet management

3. **Google API Integration** (`lib/google/`)
    - `GoogleApiClient.php` - Wrapper around Google's PHP client library
    - `Facades/Google.php` - Facade for Google API client access
    - `Providers/GoogleServiceProvider.php` - Service provider for Google client registration

4. **Laravel Integration** (`src/`)
    - `Providers/SheetsServiceProvider.php` - Main service provider for package registration
    - `Traits/GoogleSheets.php` - Trait for Eloquent models to enable user-specific Google Sheets access

5. **Configuration & Documentation**
    - `config/google.php` - Configuration template for Google API credentials
    - `docs/` - Usage documentation and examples
    - `composer.json` - Package definition with dependencies and autoloading

### Main Directories

```
├── src/                          # Core package source code
│   ├── Concerns/                 # Trait-based modular functionality
│   ├── Contracts/               # Interface definitions
│   ├── Facades/                 # Laravel facades
│   ├── Providers/               # Service providers
│   └── Traits/                  # Traits for model integration
├── lib/google/                  # Google API client wrapper
│   ├── Facades/
│   └── Providers/
├── tests/                       # Comprehensive test suite
├── docs/                        # Documentation and usage examples
├── .github/workflows/           # CI/CD automation
├── config/                      # Configuration templates
└── composer.json               # Package definition
```

### Development Practices

**Testing Strategy**:
- **Unit Tests**: Individual component testing (e.g., `SheetsCollectionTest.php`)
- **Integration Tests**: Facade and trait functionality testing (e.g., `SheetsTest.php`)
- **Mock Tests**: Google API interaction testing with mocked responses (e.g., `SheetsMockTest.php`)
- **Test Infrastructure**: Uses Orchestra Testbench for Laravel package testing and Mockery for API mocking

**CI/CD Pipeline** (`.github/workflows/`):
- **test.yml**: Runs PHPUnit tests across multiple PHP versions (8.2, 8.3, 8.4) with code coverage reporting
- **lint.yml**: Enforces code style using Laravel Pint on develop and main branches

**Code Quality**:
- PHP 8.2+ type declarations and features
- PSR-4 autoloading with clear namespace organization
- Comprehensive documentation in both code and markdown files
- Fluent interface design for method chaining

## Glossary of Codebase-Specific Terms

**SheetsClient** - `src/SheetsClient.php`  
Main implementation class that wraps Google Sheets API functionality. Implements Factory interface.

**Factory** - `src/Contracts/Factory.php`  
Core interface defining methods for Google Sheets operations like `get()`, `update()`, `append()`.

**Sheets** - `src/Facades/Sheets.php`  
Laravel facade providing static access to SheetsClient instance via service container.

**GoogleSheets** - `src/Traits/GoogleSheets.php`  
Trait for Eloquent models enabling user-specific Google Sheets access via `sheets()` method.

**SheetsValues** - `src/Concerns/SheetsValues.php`  
Trait providing CRUD operations: `all()`, `update()`, `clear()`, `append()` methods.

**SheetsCollection** - `src/Concerns/SheetsCollection.php`  
Trait for data transformation, includes `get()` and `collection()` helper methods.

**SheetsProperties** - `src/Concerns/SheetsProperties.php`  
Trait for metadata retrieval: `spreadsheetProperties()`, `sheetProperties()` methods.

**SheetsDrive** - `src/Concerns/SheetsDrive.php`  
Trait for Google Drive integration, provides `spreadsheetList()` method.

**GoogleApiClient** - `lib/google/GoogleApiClient.php`  
Wrapper class around Google's PHP client library with `make()` factory method.

**sheetsAccessToken()** - Abstract method in GoogleSheets trait  
Must be implemented by models to return user's Google API credentials array.

**spreadsheetList()** - Method returning array of spreadsheet IDs => titles  
Available via SheetsDrive trait and Sheets facade.

**all()** - `SheetsValues::all(): array`  
Retrieves all values from currently selected sheet/range as raw array.

**collection()** - `SheetsCollection::collection(array $header, array $rows): Collection`  
Transforms raw data into Laravel Collection with header-based associative arrays.

**orderAppendables()** - `SheetsValues::orderAppendables(array $values): array`  
Internal method that reorders associative arrays to match existing sheet headers.

**ranges()** - `SheetsValues::ranges(): string`  
Normalizes range strings, handles A1 notation and sheet prefixing.

**majorDimension** - Query parameter controlling data organization (ROWS/COLUMNS)  
Set via `majorDimension()` method for read/write operations.

**valueRenderOption** - Query parameter for value formatting (FORMATTED_VALUE/UNFORMATTED_VALUE)  
Configurable via `valueRenderOption()` method.

**dateTimeRenderOption** - Query parameter for date/time rendering (SERIAL_NUMBER/FORMATTED_STRING)  
Set via `dateTimeRenderOption()` method.

**setAccessToken()** - `setAccessToken(array|string $token): static`  
Method for configuring Google client authentication across facades and clients.

**make()** - `GoogleApiClient::make(string $service): mixed`  
Factory method for creating Google service instances (e.g., 'sheets', 'drive').

**macro()** - `Sheets::macro(string $name, callable $callback): void`  
Extension mechanism allowing custom methods to be added to Sheets facade.

**spreadsheetByTitle()** - `spreadsheetByTitle(string $title): static`  
Selects spreadsheet by title instead of ID, uses Drive API for resolution.

**sheetById()** - `sheetById(string $sheetId): static`  
Selects sheet by ID instead of name, resolves via sheet list lookup.

**batchUpdate** - Google Sheets API operation type for bulk value updates  
Used internally by `update()` and sheet management operations.

**A1 notation** - Google Sheets range format (e.g., "Sheet1!A1:B10")  
Handled automatically by `range()` method and internal range resolution.
