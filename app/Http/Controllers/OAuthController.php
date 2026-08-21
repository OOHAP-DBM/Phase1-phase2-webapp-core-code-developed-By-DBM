<?php

// namespace App\Http\Controllers;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Laravel\Socialite\Facades\Socialite;
// use App\Models\OauthProvider;
// use App\Models\User;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Log;

// class OAuthController extends Controller
// {
//     public function redirectToProvider($provider)
//     {
//         $providerRecord = OauthProvider::where('provider', $provider)->first();

//         if (! $providerRecord || ! $providerRecord->active) {
//             return redirect()->route('login')->withErrors(['credentials' => ucfirst($provider) . ' login is not available.']);
//         }

//         // Set runtime services config for Socialite
//         config(['services.' . $provider => [
//             'client_id' => $providerRecord->client_id,
//             'client_secret' => $providerRecord->client_secret,
//             'redirect' => $providerRecord->redirect,
//         ]]);

//         // For Google, construct the OAuth URL explicitly using the redirect URI stored in DB to avoid redirect_uri_mismatch
//         if ($provider === 'google') {
//             $state = Str::random(40);
//             session(['oauth_state' => $state]);

//             $params = [
//                 'client_id' => $providerRecord->client_id,
//                 'redirect_uri' => $providerRecord->redirect,
//                 'response_type' => 'code',
//                 'scope' => 'openid email profile',
//                 'access_type' => 'offline',
//                 'prompt' => 'consent',
//                 'state' => $state,
//             ];

//             $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

//             Log::info('OAuth redirect target (manual)', ['provider' => $provider, 'target' => $url, 'db_redirect' => $providerRecord->redirect]);

//             return redirect()->away($url);
//         }

//         // Fallback: use Socialite for other providers
//         $redirectResponse = Socialite::driver($provider)->redirect();
//         try {
//             $target = method_exists($redirectResponse, 'getTargetUrl') ? $redirectResponse->getTargetUrl() : null;
//             Log::info('OAuth redirect target', ['provider' => $provider, 'target' => $target, 'db_redirect' => $providerRecord->redirect]);
//         } catch (\Exception $e) {
//             Log::error('Unable to log OAuth redirect target', ['provider' => $provider, 'error' => $e->getMessage()]);
//         }

//         return $redirectResponse;
//     }

//     public function handleProviderCallback($provider)
//     {
//         $providerRecord = OauthProvider::where('provider', $provider)->first();

//         if (! $providerRecord || ! $providerRecord->active) {
//             return redirect()->route('login')->withErrors(['credentials' => ucfirst($provider) . ' login is not available.']);
//         }

//         config(['services.' . $provider => [
//             'client_id' => $providerRecord->client_id,
//             'client_secret' => $providerRecord->client_secret,
//             'redirect' => $providerRecord->redirect,
//         ]]);

//         try {
//             $socialUser = Socialite::driver($provider)->stateless()->user();
//         } catch (\Exception $e) {
//             return redirect()->route('login')->withErrors(['credentials' => 'Unable to authenticate with ' . ucfirst($provider) . '.']);
//         }

//         $email = $socialUser->getEmail();

//         if (! $email) {
//             return redirect()->route('login')->withErrors(['credentials' => 'No email returned from ' . ucfirst($provider) . '.']);
//         }

//         $user = User::where('email', $email)->first();

//         if (! $user) {
//             $user = User::create([
//                 'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $email,
//                 'email' => $email,
//                 'password' => Hash::make(Str::random(24)),
//             ]);
//         }

//         Auth::login($user, true);

//         return redirect()->intended('/');
//     }
// }


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\OauthProvider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OAuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        Log::info('OAuth redirect started', [
            'provider' => $provider,
        ]);

        $providerRecord = OauthProvider::where('provider', $provider)->first();

        if (! $providerRecord) {
            Log::warning('OAuth provider not found', [
                'provider' => $provider,
            ]);

            return redirect()->route('login')->withErrors([
                'credentials' => ucfirst($provider) . ' login is not available.'
            ]);
        }

        if (! $providerRecord->active) {
            Log::warning('OAuth provider is inactive', [
                'provider' => $provider,
            ]);

            return redirect()->route('login')->withErrors([
                'credentials' => ucfirst($provider) . ' login is not available.'
            ]);
        }

        Log::info('OAuth provider configuration loaded', [
            'provider' => $provider,
            'redirect' => $providerRecord->redirect,
            'client_id' => $providerRecord->client_id,
            'active' => $providerRecord->active,
        ]);

        // Set runtime services config for Socialite
        config(['services.' . $provider => [
            'client_id' => $providerRecord->client_id,
            'client_secret' => $providerRecord->client_secret,
            'redirect' => $providerRecord->redirect,
        ]]);

        Log::info('OAuth runtime configuration set', [
            'provider' => $provider,
            'redirect' => $providerRecord->redirect,
        ]);

        // Google OAuth
        if ($provider === 'google') {
            $state = Str::random(40);

            session(['oauth_state' => $state]);

            Log::info('Google OAuth state generated', [
                'provider' => $provider,
                'state_length' => strlen($state),
            ]);

            $params = [
                'client_id' => $providerRecord->client_id,
                'redirect_uri' => $providerRecord->redirect,
                'response_type' => 'code',
                'scope' => 'openid email profile',
                'access_type' => 'offline',
                'prompt' => 'consent',
                'state' => $state,
            ];

            $url = 'https://accounts.google.com/o/oauth2/v2/auth?' .
                http_build_query($params);

            // Do NOT log client_secret or other sensitive credentials.
            Log::info('OAuth redirect target generated', [
                'provider' => $provider,
                'target' => $url,
                'db_redirect' => $providerRecord->redirect,
            ]);

            return redirect()->away($url);
        }

        // Fallback: Socialite
        try {
            Log::info('Creating Socialite OAuth redirect', [
                'provider' => $provider,
            ]);

            $redirectResponse = Socialite::driver($provider)->redirect();

            $target = method_exists($redirectResponse, 'getTargetUrl')
                ? $redirectResponse->getTargetUrl()
                : null;

            Log::info('Socialite OAuth redirect generated', [
                'provider' => $provider,
                'target' => $target,
                'db_redirect' => $providerRecord->redirect,
            ]);

            return $redirectResponse;

        } catch (\Throwable $e) {
            Log::error('OAuth redirect failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('login')->withErrors([
                'credentials' => 'Unable to start ' . ucfirst($provider) . ' login.'
            ]);
        }
    }

    public function handleProviderCallback($provider)
    {
        Log::info('OAuth callback received', [
            'provider' => $provider,
            'url' => request()->fullUrl(),
            'has_code' => request()->has('code'),
            'has_error' => request()->has('error'),
        ]);

        // Log OAuth provider errors returned by Google/etc.
        if (request()->has('error')) {
            Log::error('OAuth provider returned an error', [
                'provider' => $provider,
                'error' => request()->get('error'),
                'error_description' => request()->get('error_description'),
            ]);

            return redirect()->route('login')->withErrors([
                'credentials' => 'OAuth authentication was cancelled or failed.'
            ]);
        }

        $providerRecord = OauthProvider::where('provider', $provider)->first();

        if (! $providerRecord) {
            Log::warning('OAuth callback provider not found', [
                'provider' => $provider,
            ]);

            return redirect()->route('login')->withErrors([
                'credentials' => ucfirst($provider) . ' login is not available.'
            ]);
        }

        if (! $providerRecord->active) {
            Log::warning('OAuth callback provider is inactive', [
                'provider' => $provider,
            ]);

            return redirect()->route('login')->withErrors([
                'credentials' => ucfirst($provider) . ' login is not available.'
            ]);
        }

        Log::info('OAuth callback provider configuration loaded', [
            'provider' => $provider,
            'redirect' => $providerRecord->redirect,
            'client_id' => $providerRecord->client_id,
        ]);

        config(['services.' . $provider => [
            'client_id' => $providerRecord->client_id,
            'client_secret' => $providerRecord->client_secret,
            'redirect' => $providerRecord->redirect,
        ]]);

        Log::info('Attempting Socialite authentication', [
            'provider' => $provider,
        ]);

        try {
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->user();

            Log::info('Socialite authentication successful', [
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'email' => $socialUser->getEmail(),
                'name' => $socialUser->getName(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Socialite authentication failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('login')->withErrors([
                'credentials' => 'Unable to authenticate with ' .
                    ucfirst($provider) . '.'
            ]);
        }

        $email = $socialUser->getEmail();

        if (! $email) {
            Log::error('OAuth provider did not return an email', [
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'name' => $socialUser->getName(),
            ]);

            return redirect()->route('login')->withErrors([
                'credentials' => 'No email returned from ' .
                    ucfirst($provider) . '.'
            ]);
        }

        Log::info('Looking up OAuth user', [
            'provider' => $provider,
            'email' => $email,
        ]);

        $user = User::where('email', $email)->first();

        if (! $user) {
            Log::info('OAuth user does not exist, creating user', [
                'provider' => $provider,
                'email' => $email,
            ]);

            try {
                $user = User::create([
                    'name' => $socialUser->getName()
                        ?? $socialUser->getNickname()
                        ?? $email,

                    'email' => $email,
                    'password' => Hash::make(Str::random(24)),
                ]);

                Log::info('OAuth user created successfully', [
                    'provider' => $provider,
                    'user_id' => $user->id,
                    'email' => $email,
                ]);

            } catch (\Throwable $e) {
                Log::error('Failed to create OAuth user', [
                    'provider' => $provider,
                    'email' => $email,
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                return redirect()->route('login')->withErrors([
                    'credentials' => 'Unable to create your account.'
                ]);
            }

        } else {
            Log::info('Existing OAuth user found', [
                'provider' => $provider,
                'user_id' => $user->id,
                'email' => $email,
            ]);
        }

        try {
            Auth::login($user, true);

            Log::info('OAuth login successful', [
                'provider' => $provider,
                'user_id' => $user->id,
                'email' => $email,
            ]);

        } catch (\Throwable $e) {
            Log::error('Auth::login failed after OAuth authentication', [
                'provider' => $provider,
                'user_id' => $user->id ?? null,
                'email' => $email,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('login')->withErrors([
                'credentials' => 'Unable to log you in.'
            ]);
        }

        Log::info('Redirecting authenticated OAuth user', [
            'provider' => $provider,
            'user_id' => $user->id,
            'redirect' => session()->get('url.intended', '/'),
        ]);

        return redirect()->intended('/');
    }
}
