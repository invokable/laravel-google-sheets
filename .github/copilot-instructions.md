# Repository Overview
This repository, `laravel-google-sheets`, provides a Laravel package for interacting with Google Sheets via the Google Sheets API. It supports Service Account, OAuth, and API key authentication, and exposes a fluent, macroable API for spreadsheet and sheet manipulation, including reading, writing, clearing, updating, and appending values.

## Copilot Instructions

### General Purpose
- This package is a Laravel PHP package for reading and writing Google Sheets data.
- It provides a fluent interface via a Facade (`Sheets`) and can be extended with macros.
- It supports both Laravel and non-Laravel (plain PHP) usage.

### Main Concepts
- Always require Google Sheets and optionally Google Drive API credentials set in `.env` or config files.
- Authentication can be Service Account (default), OAuth (user-specific), or API key (for public sheets only).
- The main interface is the `Sheets` Facade, with methods: `setAccessToken`, `spreadsheet`, `sheet`, `all`, `get`, `collection`, `update`, `append`, and more.

### Best Practices
- Prefer Service Account for automated server-side access.
- Use OAuth if users need to access their own spreadsheets.
- Use the macro feature to extend the API for custom needs (see `docs/macro.md`).
- Always check for valid tokens before making requests.
- Handle API exceptions gracefully and log errors.

### Code Style
- Follow PSR-12 coding standards.
- Use type hints and PHPDoc blocks generously.
- Write PHP in English for comments, docblocks, and documentation.

### Contribution Guidelines
- Pull Requests should include tests where possible.
- Document any new or changed public APIs in the README.
- Keep method and variable names descriptive and in English.
- Use existing traits and contracts for new Sheet-related features.

### Example Usage
```php
use Revolution\Google\Sheets\Facades\Sheets;

// Set access token and retrieve sheet data
$values = Sheets::setAccessToken($token)
    ->spreadsheet('spreadsheetId')
    ->sheet('Sheet 1')
    ->all();
```

For more, see [README.md](../README.md) and [docs/macro.md](../docs/macro.md).

---
This file is auto-generated for GitHub Copilot. If you update repository structure or main usage, please update this file accordingly.