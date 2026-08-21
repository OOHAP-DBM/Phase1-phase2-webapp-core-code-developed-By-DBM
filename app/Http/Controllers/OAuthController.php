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

        return Socialite::driver($provider)->redirect();
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
