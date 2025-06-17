# OAuth by Socialite

https://github.com/kawax/google-sheets-project/blob/6.x/app/Http/Controllers/LoginController.php

```php
public function redirect()
{
    return Socialite::driver('google')
                    ->scopes(config('google.scopes'))
                    ->with([
                        'access_type'     => config('google.access_type'),
                        'approval_prompt' => config('google.approval_prompt'),
                    ])
                    ->redirect();
}

public function callback()
{
    if (!request()->has('code')) {
        return redirect('/');
    }

    /**
     * @var \Laravel\Socialite\Two\User $user
     */
    $user = Socialite::driver('google')->user();

    /**
     * @var \App\User $loginUser
     */
    $loginUser = User::updateOrCreate(
        [
            'email' => $user->email,
        ],
        [
            'name'          => $user->name,
            'email'         => $user->email,
            'access_token'  => $user->token,
            'refresh_token' => $user->refreshToken,
            'expires_in'    => $user->expiresIn,
        ]);

    auth()->login($loginUser, false);

    return redirect('/home');
}
```
