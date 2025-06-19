# OAuth 2.0 Authentication

OAuth 2.0 authentication allows users to grant your application access to their personal Google Sheets. This method is
ideal for user-facing applications where each user needs to access their own spreadsheets.

## When to Use OAuth Authentication

- **User-centric applications**: Users need to access their own Google Sheets
- **Multi-tenant applications**: Different users accessing different spreadsheets
- **Personal data access**: Reading/writing data from user's personal Google account
- **Desktop or web applications**: Apps where user interaction is available

## Prerequisites

- A Google Cloud Console project
- Google Sheets API and Google Drive API enabled
- Laravel Socialite package (recommended)

## Step 1: Configure Google Cloud Console

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project or create a new one
3. Navigate to **APIs & Services** > **Library**
4. Enable the following APIs:
    - **Google Sheets API**
    - **Google Drive API**

## Step 2: Create OAuth 2.0 Credentials

1. Go to **APIs & Services** > **Credentials**
2. Click **Create Credentials** > **OAuth client ID**
3. If prompted, configure the OAuth consent screen:
    - Choose **External** for public applications
    - Fill in required fields (app name, user support email, developer contact)
    - Add scopes: `https://www.googleapis.com/auth/spreadsheets` and `https://www.googleapis.com/auth/drive`
4. For application type, choose **Web application**
5. Add authorized redirect URIs:
    - For development: `http://localhost:8000/auth/google/callback`
    - For production: `https://yourdomain.com/auth/google/callback`
6. Click **Create**
7. Copy the **Client ID** and **Client Secret**

## Step 3: Configure Laravel Environment

Add the following to your `.env` file:

```env
GOOGLE_CLIENT_ID=your-client-id-here
GOOGLE_CLIENT_SECRET=your-client-secret-here
GOOGLE_REDIRECT=http://localhost:8000/auth/google/callback
```

Update your `config/google.php`:

```php
'client_id' => env('GOOGLE_CLIENT_ID', ''),
'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
'redirect_uri' => env('GOOGLE_REDIRECT', ''),
'scopes' => [
    \Google\Service\Sheets::SPREADSHEETS,
    \Google\Service\Drive::DRIVE,
],
'access_type' => 'offline', // Required for refresh tokens
'prompt' => 'consent select_account',
```

## Step 4: Install and Configure Laravel Socialite

```bash
composer require laravel/socialite
```

Add to `config/services.php`:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT'),
],
```

## Step 5: Implement OAuth Flow

Create authentication controller:

```php
// app/Http/Controllers/AuthController.php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(config('google.scopes'))
            ->with([
                'access_type' => config('google.access_type'),
                'prompt' => config('google.prompt'),
            ])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_access_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'google_expires_in' => $googleUser->expiresIn,
                    'google_token_created' => now()->timestamp,
                ]
            );

            auth()->login($user);

            return redirect('/dashboard')->with('success', 'Successfully connected to Google!');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
```

## Step 6: Add Routes

```php
// routes/web.php
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
```

## Step 7: Update User Model

Add the necessary fields to store Google tokens:

```php
// database/migrations/add_google_tokens_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->text('google_access_token')->nullable();
    $table->text('google_refresh_token')->nullable();
    $table->integer('google_expires_in')->nullable();
    $table->integer('google_token_created')->nullable();
});
```

Update your User model:

```php
// app/Models/User.php
protected $fillable = [
    'name',
    'email',
    'password',
    'google_access_token',
    'google_refresh_token',
    'google_expires_in',
    'google_token_created',
];

protected $hidden = [
    'password',
    'remember_token',
    'google_access_token',
    'google_refresh_token',
];

public function getGoogleTokenArray(): array
{
    return [
        'access_token' => $this->google_access_token,
        'refresh_token' => $this->google_refresh_token,
        'expires_in' => $this->google_expires_in,
        'created' => $this->google_token_created,
    ];
}

public function hasValidGoogleToken(): bool
{
    return !empty($this->google_access_token) && !empty($this->google_refresh_token);
}
```

## Step 8: Using OAuth with Google Sheets

```php
use Revolution\Google\Sheets\Facades\Sheets;

// In your controller or service
public function getSheetData(Request $request)
{
    $user = $request->user();
    
    if (!$user->hasValidGoogleToken()) {
        return redirect()->route('google.redirect');
    }

    try {
        $token = $user->getGoogleTokenArray();

        $values = Sheets::setAccessToken($token)
            ->spreadsheet('user-spreadsheet-id')
            ->sheet('Sheet1')
            ->all();
            
        return view('sheets.data', compact('values'));
    } catch (\Exception $e) {
        // Token might be expired, redirect to re-authenticate
        if (str_contains($e->getMessage(), 'invalid_grant') || str_contains($e->getMessage(), 'unauthorized')) {
            return redirect()->route('google.redirect');
        }

        throw $e;
    }
}
```

## Advanced Usage

### Token Refresh Handling

The package automatically handles token refresh when using `setAccessToken()` with a valid refresh token:

```php
$token = [
    'access_token' => $user->google_access_token,
    'refresh_token' => $user->google_refresh_token,
    'expires_in' => $user->google_expires_in,
    'created' => $user->google_token_created,
];

// This will automatically refresh the token if expired
Sheets::setAccessToken($token)->spreadsheet('id')->sheet('Sheet1')->all();

// Get the updated token after refresh
$updatedToken = Sheets::getAccessToken();
if ($updatedToken) {
    $user->update([
        'google_access_token' => $updatedToken['access_token'],
        'google_token_created' => time(),
    ]);
}
```

### Middleware for Authentication

Create middleware to ensure users are authenticated with Google:

```php
// app/Http/Middleware/RequireGoogleAuth.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireGoogleAuth
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->hasValidGoogleToken()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Google authentication required'], 401);
            }

            return redirect()->route('google.redirect');
        }

        return $next($request);
    }
}
```

## Security Considerations

### 1. Token Storage

- Store tokens securely in the database
- Use Laravel's built-in encryption for sensitive fields
- Never expose tokens in client-side code

### 2. Scope Management

- Only request necessary scopes
- Use least-privilege principle
- Clearly explain to users what access you need

### 3. Error Handling

- Handle expired tokens gracefully
- Provide clear re-authentication flows
- Log authentication errors for monitoring

## Troubleshooting

### Common OAuth Errors

**"redirect_uri_mismatch"**

- Ensure redirect URI in Google Console matches exactly with your application
- Check for http vs https mismatches
- Verify trailing slashes match

**"invalid_grant" or "unauthorized"**

- Token has expired and refresh failed
- Redirect user to re-authenticate
- Check if refresh token is available

**"access_denied"**

- User denied permission
- Handle gracefully with appropriate messaging
- Provide option to retry authentication

### Testing OAuth Flow

Create a test route to verify your OAuth setup:

```php
Route::get('/test-oauth', function (Request $request) {
    $user = $request->user();
    
    if (!$user->hasValidGoogleToken()) {
        return 'No Google token available. <a href="' . route('google.redirect') . '">Authenticate</a>';
    }
    
    try {
        $token = $user->getGoogleTokenArray();
        $sheets = Sheets::setAccessToken($token)->spreadsheetList();
        
        return 'OAuth working! Found ' . count($sheets) . ' spreadsheets.';
    } catch (\Exception $e) {
        return 'OAuth error: ' . $e->getMessage();
    }
})->middleware('auth');
```
