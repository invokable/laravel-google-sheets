# Service Account Authentication

Service Account authentication is ideal for server-to-server access to Google Sheets that your application owns or has been granted access to. This method doesn't require user interaction and is perfect for automated systems, background jobs, or applications that need to access specific spreadsheets programmatically.

## When to Use Service Account Authentication

- **Server-to-server access**: Your application needs to access Google Sheets without user interaction
- **Automated systems**: Background jobs, cron tasks, or automated reporting
- **Fixed spreadsheet access**: Working with specific spreadsheets that you control
- **Production applications**: Reliable authentication without user consent flows

## Prerequisites

- A Google Cloud Console project
- Google Sheets API and Google Drive API enabled
- Admin access to create Service Accounts

## Step 1: Create a Google Cloud Project

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Note your project ID for later use

## Step 2: Enable Required APIs

1. In the Google Cloud Console, navigate to **APIs & Services** > **Library**
2. Search for and enable the following APIs:
   - **Google Sheets API**
   - **Google Drive API** (required for accessing spreadsheet metadata and listing)

## Step 3: Create a Service Account

1. Go to **APIs & Services** > **Credentials**
2. Click **Create Credentials** > **Service Account**
3. Fill in the service account details:
   - **Service account name**: A descriptive name (e.g., "Laravel Sheets App")
   - **Service account ID**: Will be auto-generated
   - **Description**: Optional description of the service account's purpose
4. Click **Create and Continue**
5. Skip the optional "Grant this service account access to project" section by clicking **Continue**
6. Skip the optional "Grant users access to this service account" section by clicking **Done**

## Step 4: Generate Service Account Key

1. In the **Credentials** page, find your newly created service account
2. Click on the service account email to open its details
3. Go to the **Keys** tab
4. Click **Add Key** > **Create new key**
5. Choose **JSON** as the key type
6. Click **Create**
7. The JSON key file will be automatically downloaded to your computer

## Step 5: Store the Service Account Key

1. Move the downloaded JSON file to your Laravel project's `storage/app/` directory
2. Rename it to something descriptive like `google-service-account.json`
3. **Important**: Add this file to your `.gitignore` to prevent committing credentials to version control

```bash
# Add to .gitignore
storage/app/google-service-account.json
```

## Step 6: Configure Laravel Environment

Add the following to your `.env` file:

```env
GOOGLE_SERVICE_ENABLED=true
GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION=storage/app/google-service-account.json
```

**Important**: Ensure your `config/google.php` includes the required scopes setting for both OAuth and Service Account authentication:

```php
'scopes' => [
    \Google\Service\Sheets::SPREADSHEETS,
    \Google\Service\Drive::DRIVE,
],
```

## Step 7: Share Spreadsheets with Service Account

For each Google Sheets spreadsheet you want to access:

1. Open the spreadsheet in Google Sheets
2. Click the **Share** button
3. In the service account JSON file, find the `client_email` field
4. Share the spreadsheet with this email address
5. Grant appropriate permissions:
   - **Viewer**: Read-only access
   - **Editor**: Read and write access
   - **Owner**: Full control (not recommended for service accounts)

Example service account email format:
```
your-service-account@your-project-id.iam.gserviceaccount.com
```

## Step 8: Usage in Laravel

Once configured, you can use the service account authentication seamlessly:

```php
use Revolution\Google\Sheets\Facades\Sheets;

// Service account authentication is automatically handled
// No need to set access tokens manually

$values = Sheets::spreadsheet('your-spreadsheet-id')
    ->sheet('Sheet1')
    ->all();

// Or using spreadsheet title (requires Drive API access)
$values = Sheets::spreadsheetByTitle('My Spreadsheet')
    ->sheet('Sheet1')
    ->all();
```

## Security Best Practices

### 1. Limit Service Account Permissions
- Only grant necessary permissions to spreadsheets
- Use **Editor** permission instead of **Owner** when possible
- Regularly audit which spreadsheets have access

### 2. Secure Key Storage
- Never commit service account keys to version control
- Use environment variables for key file paths
- Consider using encrypted storage for production environments
- Rotate service account keys periodically

### 3. Production Deployment
For production environments, consider these additional security measures:

- Store the JSON key in a secure location outside the web root
- Use environment-specific service accounts (dev, staging, production)
- Monitor service account usage through Google Cloud Console
- Set up logging and alerts for suspicious activity

## Environment Variables Reference

| Variable | Description | Example |
|----------|-------------|---------|
| `GOOGLE_SERVICE_ENABLED` | Enable service account authentication | `true` |
| `GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION` | Path to service account JSON file | `storage/app/google-service-account.json` |

## Troubleshooting

### Common Issues

**"The caller does not have permission" error**
- Ensure the spreadsheet is shared with the service account email
- Check that the service account has appropriate permissions (Editor for write operations)

**"File not found" error**
- Verify the path to the JSON key file is correct
- Ensure the file exists and is readable by the web server

**"API not enabled" error**
- Confirm Google Sheets API and Google Drive API are enabled in Google Cloud Console
- Wait a few minutes after enabling APIs for changes to propagate

**"Invalid credentials" error**
- Verify the JSON key file is valid and not corrupted
- Ensure the service account hasn't been deleted or disabled
- Check that the project ID matches your Google Cloud project

### Testing Your Setup

Create a simple test route to verify your service account setup:

```php
// routes/web.php
Route::get('/test-sheets', function () {
    try {
        $sheets = Sheets::spreadsheetList();
        return response()->json([
            'status' => 'success',
            'message' => 'Service account authentication working',
            'spreadsheet_count' => count($sheets)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});
```

## Advanced Configuration

### Using JSON String in Environment Variable

You can store the service account credentials as a JSON string in your environment variable and decode it in your configuration file. This method is particularly well-suited for deployment scenarios like GitHub Actions, as it allows the entire service account credentials to be stored as a single secret.

**Step 1: Add JSON string to your `.env` file:**

```env
GOOGLE_SERVICE_ENABLED=true
GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION='{"type": "service_account", "project_id": "your-project-id", "private_key_id": "your-private-key-id", "private_key": "-----BEGIN PRIVATE KEY-----\nYOUR_PRIVATE_KEY_HERE\n-----END PRIVATE KEY-----\n", "client_email": "your-service-account@your-project-id.iam.gserviceaccount.com", "client_id": "your-client-id", "auth_uri": "https://accounts.google.com/o/oauth2/auth", "token_uri": "https://oauth2.googleapis.com/token", "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs", "client_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs/your-service-account%40your-project-id.iam.gserviceaccount.com"}'
```

**Step 2: Update your `config/google.php` to decode the JSON string:**

```php
// config/google.php
'service' => [
    'enable' => env('GOOGLE_SERVICE_ENABLED', false),
    'file' => json_decode(env('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION', ''), true),
],
```

This approach eliminates the need to store a separate JSON file and makes deployment easier, especially in containerized environments or CI/CD pipelines.

### Using Credentials Array Instead of File

You can also pass credentials as an array instead of a file path:

```php
// config/google.php
'service' => [
    'enable' => true,
    'file' => [
        'type' => 'service_account',
        'project_id' => env('GOOGLE_PROJECT_ID'),
        'private_key_id' => env('GOOGLE_PRIVATE_KEY_ID'),
        'private_key' => env('GOOGLE_PRIVATE_KEY'),
        'client_email' => env('GOOGLE_CLIENT_EMAIL'),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
        'token_uri' => 'https://oauth2.googleapis.com/token',
        'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
        'client_x509_cert_url' => env('GOOGLE_CLIENT_CERT_URL'),
    ],
],
```

### Multiple Service Accounts

For applications that need to access different sets of spreadsheets, you can configure multiple service accounts and switch between them:

```php
// Create a separate client for a different service account
$client = new \Revolution\Google\Client\GoogleApiClient([
    'service' => [
        'enable' => true,
        'file' => storage_path('app/another-service-account.json'),
    ],
]);

$sheets = new \Revolution\Google\Sheets\SheetsClient();
$sheets->setService($client->make('sheets'));
```

This setup allows you to manage different authentication contexts for different parts of your application.