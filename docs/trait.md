# GoogleSheets Trait

Add `GoogleSheets` trait to User model.

```php
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Revolution\Google\Sheets\Traits\GoogleSheets;

class User extends Authenticatable
{
    use Notifiable;
    use GoogleSheets;

    /**
     * Get the Access Token
     *
     * @return string|array
     */
    protected function sheetsAccessToken()
    {
        return [
            'access_token'  => $this->access_token,
            'refresh_token' => $this->refresh_token,
            'expires_in'    => $this->expires_in,
            'created'       => $this->created->timestamp,
        ];
    }
}
```

Add `sheetsAccessToken()`(abstract) for access_token.

Trait has `sheets()` that returns Sheets instance.

```php
use Revolution\Google\Client\Facades\Google;
use Revolution\Google\Sheets\Facades\Sheets;

    public function __invoke(Request $request)
    {
        // Facade
        //        $token = $request->user()->access_token;
        //
        //        Google::setAccessToken($token);
        //
        //        $spreadsheets = Sheets::setService(Google::make('sheets'))
        //                              ->setDriveService(Google::make('drive'))
        //                              ->spreadsheetList();

        // GoogleSheets Trait
        $spreadsheets = $request->user()
                                ->sheets()
                                ->spreadsheetList();

        return view('sheets.index')->with(compact('spreadsheets'));
    }
```

## Already sheets() exists

```php
use GoogleSheets { 
    GoogleSheets::sheets as googlesheets;
}
```
