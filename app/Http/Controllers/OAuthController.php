<?php

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
        $providerRecord = OauthProvider::where('provider', $provider)->first();

        if (! $providerRecord || ! $providerRecord->active) {
            return redirect()->route('login')->withErrors(['credentials' => ucfirst($provider) . ' login is not available.']);
        }

        // Set runtime services config for Socialite
        config(['services.' . $provider => [
            'client_id' => $providerRecord->client_id,
            'client_secret' => $providerRecord->client_secret,
            'redirect' => $providerRecord->redirect,
        ]]);

        // For Google, construct the OAuth URL explicitly using the redirect URI stored in DB to avoid redirect_uri_mismatch
        if ($provider === 'google') {
            $state = Str::random(40);
            session(['oauth_state' => $state]);

            $params = [
                'client_id' => $providerRecord->client_id,
                'redirect_uri' => $providerRecord->redirect,
                'response_type' => 'code',
                'scope' => 'openid email profile',
                'access_type' => 'offline',
                'prompt' => 'consent',
                'state' => $state,
            ];

            $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

            Log::info('OAuth redirect target (manual)', ['provider' => $provider, 'target' => $url, 'db_redirect' => $providerRecord->redirect]);

            return redirect()->away($url);
        }

        // Fallback: use Socialite for other providers
        $redirectResponse = Socialite::driver($provider)->redirect();
        try {
            $target = method_exists($redirectResponse, 'getTargetUrl') ? $redirectResponse->getTargetUrl() : null;
            Log::info('OAuth redirect target', ['provider' => $provider, 'target' => $target, 'db_redirect' => $providerRecord->redirect]);
        } catch (\Exception $e) {
            Log::error('Unable to log OAuth redirect target', ['provider' => $provider, 'error' => $e->getMessage()]);
        }

        return $redirectResponse;
    }

    public function handleProviderCallback($provider)
    {
        $providerRecord = OauthProvider::where('provider', $provider)->first();

        if (! $providerRecord || ! $providerRecord->active) {
            return redirect()->route('login')->withErrors(['credentials' => ucfirst($provider) . ' login is not available.']);
        }

        config(['services.' . $provider => [
            'client_id' => $providerRecord->client_id,
            'client_secret' => $providerRecord->client_secret,
            'redirect' => $providerRecord->redirect,
        ]]);

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['credentials' => 'Unable to authenticate with ' . ucfirst($provider) . '.']);
        }

        $email = $socialUser->getEmail();

        if (! $email) {
            return redirect()->route('login')->withErrors(['credentials' => 'No email returned from ' . ucfirst($provider) . '.']);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $email,
                'email' => $email,
                'password' => Hash::make(Str::random(24)),
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended('/');
    }
}
