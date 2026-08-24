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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\VendorProfile;

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

    // public function handleProviderCallback($provider)
    // {
    //      Log::info('!!! GOOGLE CALLBACK REACHED LARAVEL !!!', [
    //     'provider' => $provider,
    //     'url' => request()->fullUrl(),
    //     'query' => request()->query(),
    // ]);
    //     Log::info('OAuth callback received', [
    //         'provider' => $provider,
    //         'url' => request()->fullUrl(),
    //         'has_code' => request()->has('code'),
    //         'has_error' => request()->has('error'),
    //     ]);

    //     // Log OAuth provider errors returned by Google/etc.
    //     if (request()->has('error')) {
    //         Log::error('OAuth provider returned an error', [
    //             'provider' => $provider,
    //             'error' => request()->get('error'),
    //             'error_description' => request()->get('error_description'),
    //         ]);

    //         return redirect()->route('login')->withErrors([
    //             'credentials' => 'OAuth authentication was cancelled or failed.'
    //         ]);
    //     }

    //     $providerRecord = OauthProvider::where('provider', $provider)->first();

    //     if (! $providerRecord) {
    //         Log::warning('OAuth callback provider not found', [
    //             'provider' => $provider,
    //         ]);

    //         return redirect()->route('login')->withErrors([
    //             'credentials' => ucfirst($provider) . ' login is not available.'
    //         ]);
    //     }

    //     if (! $providerRecord->active) {
    //         Log::warning('OAuth callback provider is inactive', [
    //             'provider' => $provider,
    //         ]);

    //         return redirect()->route('login')->withErrors([
    //             'credentials' => ucfirst($provider) . ' login is not available.'
    //         ]);
    //     }

    //     Log::info('OAuth callback provider configuration loaded', [
    //         'provider' => $provider,
    //         'redirect' => $providerRecord->redirect,
    //         'client_id' => $providerRecord->client_id,
    //     ]);

    //     config(['services.' . $provider => [
    //         'client_id' => $providerRecord->client_id,
    //         'client_secret' => $providerRecord->client_secret,
    //         'redirect' => $providerRecord->redirect,
    //     ]]);

    //     Log::info('Attempting Socialite authentication', [
    //         'provider' => $provider,
    //     ]);

    //     try {
    //         $socialUser = Socialite::driver($provider)
    //             ->stateless()
    //             ->user();

    //         Log::info('Socialite authentication successful', [
    //             'provider' => $provider,
    //             'provider_id' => $socialUser->getId(),
    //             'email' => $socialUser->getEmail(),
    //             'name' => $socialUser->getName(),
    //         ]);

    //     } catch (\Throwable $e) {
    //         Log::error('Socialite authentication failed', [
    //             'provider' => $provider,
    //             'error' => $e->getMessage(),
    //             'exception' => get_class($e),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //         ]);

    //         return redirect()->route('login')->withErrors([
    //             'credentials' => 'Unable to authenticate with ' .
    //                 ucfirst($provider) . '.'
    //         ]);
    //     }

    //     $email = $socialUser->getEmail();

    //     if (! $email) {
    //         Log::error('OAuth provider did not return an email', [
    //             'provider' => $provider,
    //             'provider_id' => $socialUser->getId(),
    //             'name' => $socialUser->getName(),
    //         ]);

    //         return redirect()->route('login')->withErrors([
    //             'credentials' => 'No email returned from ' .
    //                 ucfirst($provider) . '.'
    //         ]);
    //     }

    //     Log::info('Looking up OAuth user', [
    //         'provider' => $provider,
    //         'email' => $email,
    //     ]);

    //     $user = User::where('email', $email)->first();

    //     if (! $user) {
    //         Log::info('OAuth user does not exist, creating user', [
    //             'provider' => $provider,
    //             'email' => $email,
    //         ]);

    //         try {
    //             $user = User::create([
    //                 'name' => $socialUser->getName()
    //                     ?? $socialUser->getNickname()
    //                     ?? $email,

    //                 'email' => $email,
    //                 'password' => Hash::make(Str::random(24)),
    //             ]);

    //             Log::info('OAuth user created successfully', [
    //                 'provider' => $provider,
    //                 'user_id' => $user->id,
    //                 'email' => $email,
    //             ]);

    //         } catch (\Throwable $e) {
    //             Log::error('Failed to create OAuth user', [
    //                 'provider' => $provider,
    //                 'email' => $email,
    //                 'error' => $e->getMessage(),
    //                 'exception' => get_class($e),
    //                 'file' => $e->getFile(),
    //                 'line' => $e->getLine(),
    //             ]);

    //             return redirect()->route('login')->withErrors([
    //                 'credentials' => 'Unable to create your account.'
    //             ]);
    //         }

    //     } else {
    //         Log::info('Existing OAuth user found', [
    //             'provider' => $provider,
    //             'user_id' => $user->id,
    //             'email' => $email,
    //         ]);
    //     }

    //     try {
    //         Auth::login($user, true);

    //         Log::info('OAuth login successful', [
    //             'provider' => $provider,
    //             'user_id' => $user->id,
    //             'email' => $email,
    //         ]);

    //     } catch (\Throwable $e) {
    //         Log::error('Auth::login failed after OAuth authentication', [
    //             'provider' => $provider,
    //             'user_id' => $user->id ?? null,
    //             'email' => $email,
    //             'error' => $e->getMessage(),
    //             'exception' => get_class($e),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //         ]);

    //         return redirect()->route('login')->withErrors([
    //             'credentials' => 'Unable to log you in.'
    //         ]);
    //     }

    //     Log::info('Redirecting authenticated OAuth user', [
    //         'provider' => $provider,
    //         'user_id' => $user->id,
    //         'redirect' => session()->get('url.intended', '/'),
    //     ]);

    //     return redirect()->intended('/');
    // }
    public function handleProviderCallback($provider)
{
    /*
    |--------------------------------------------------------------------------
    | OAuth Callback Started
    |--------------------------------------------------------------------------
    */

    Log::info('!!! GOOGLE CALLBACK REACHED LARAVEL !!!', [
        'provider' => $provider,
        'url' => request()->fullUrl(),
        'query' => request()->query(),
    ]);

    Log::info('OAuth callback received', [
        'provider' => $provider,
        'url' => request()->fullUrl(),
        'has_code' => request()->has('code'),
        'has_error' => request()->has('error'),
    ]);


    /*
    |--------------------------------------------------------------------------
    | Check OAuth Provider Error
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Get Provider Configuration
    |--------------------------------------------------------------------------
    */

    $providerRecord = OauthProvider::where(
        'provider',
        $provider
    )->first();

    if (! $providerRecord) {

        Log::warning('OAuth callback provider not found', [
            'provider' => $provider,
        ]);

        return redirect()->route('login')->withErrors([
            'credentials' =>
                ucfirst($provider) . ' login is not available.'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Check Provider Active
    |--------------------------------------------------------------------------
    */

    if (! $providerRecord->active) {

        Log::warning('OAuth callback provider is inactive', [
            'provider' => $provider,
        ]);

        return redirect()->route('login')->withErrors([
            'credentials' =>
                ucfirst($provider) . ' login is not available.'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Provider Configuration Loaded
    |--------------------------------------------------------------------------
    */

    Log::info('OAuth callback provider configuration loaded', [
        'provider' => $provider,
        'redirect' => $providerRecord->redirect,
        'client_id' => $providerRecord->client_id,
    ]);


    /*
    |--------------------------------------------------------------------------
    | Set Runtime Socialite Configuration
    |--------------------------------------------------------------------------
    */

    config([
        'services.' . $provider => [
            'client_id' => $providerRecord->client_id,
            'client_secret' => $providerRecord->client_secret,
            'redirect' => $providerRecord->redirect,
        ]
    ]);


    /*
    |--------------------------------------------------------------------------
    | Authenticate With Google / Socialite
    |--------------------------------------------------------------------------
    */

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
            'credentials' =>
                'Unable to authenticate with ' .
                ucfirst($provider) . '.'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Get Email From Google
    |--------------------------------------------------------------------------
    */

    $email = $socialUser->getEmail();

    if (! $email) {

        Log::error('OAuth provider did not return an email', [
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'name' => $socialUser->getName(),
        ]);

        return redirect()->route('login')->withErrors([
            'credentials' =>
                'No email returned from ' .
                ucfirst($provider) . '.'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Email
    |--------------------------------------------------------------------------
    |
    | Email addresses should be compared consistently.
    |
    */

    $email = strtolower(trim($email));


    Log::info('Looking up OAuth user', [
        'provider' => $provider,
        'email' => $email,
    ]);


    /*
    |--------------------------------------------------------------------------
    | Check User By Email
    |--------------------------------------------------------------------------
    */

    $user = User::where('email', $email)->first();


    /*
    |--------------------------------------------------------------------------
    | EXISTING USER
    |--------------------------------------------------------------------------
    |
    | If user already exists:
    |
    | - Do NOT ask for role
    | - Do NOT change role
    | - Do NOT create another account
    | - Login existing user
    |
    */

    if ($user) {

        Log::info('Existing OAuth user found', [
            'provider' => $provider,
            'user_id' => $user->id,
            'email' => $email,
            'roles' => $user->getRoleNames()->toArray(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Login Existing User
        |--------------------------------------------------------------------------
        */

        try {

            Auth::login($user, true);

            Log::info('OAuth login successful for existing user', [
                'provider' => $provider,
                'user_id' => $user->id,
                'email' => $email,
                'roles' => $user->getRoleNames()->toArray(),
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Auth::login failed for existing OAuth user',
                [
                    'provider' => $provider,
                    'user_id' => $user->id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );

            return redirect()->route('login')->withErrors([
                'credentials' => 'Unable to log you in.'
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Merge Guest Data
        |--------------------------------------------------------------------------
        */

        session([
            'merge_guest_data' => true
        ]);


        /*
        |--------------------------------------------------------------------------
        | Existing Vendor
        |--------------------------------------------------------------------------
        */

        if ($user->hasRole('vendor')) {

            Log::info(
                'Existing OAuth vendor detected. Redirecting to vendor onboarding.',
                [
                    'user_id' => $user->id,
                    'email' => $email,
                ]
            );

            return redirect()
                ->route('vendor.onboarding.contact-details');
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Customer / Other User
        |--------------------------------------------------------------------------
        */

        Log::info('Redirecting existing OAuth user', [
            'provider' => $provider,
            'user_id' => $user->id,
            'email' => $email,
            'redirect' => session()->get('url.intended', '/'),
        ]);

        return redirect()->intended('/');
    }


    /*
    |--------------------------------------------------------------------------
    | NEW USER
    |--------------------------------------------------------------------------
    |
    | User does NOT exist.
    |
    | IMPORTANT:
    |
    | We DO NOT create the account here.
    |
    | We first ask the user:
    |
    |   Customer
    |   Vendor
    |
    | After the user selects a role, completeOAuthSignup()
    | will create the account and assign the selected role.
    |
    */


    Log::info(
        'OAuth user does not exist. Preparing role selection.',
        [
            'provider' => $provider,
            'email' => $email,
            'provider_id' => $socialUser->getId(),
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Store OAuth User Data In Session
    |--------------------------------------------------------------------------
    */

    session([
        'oauth_signup' => [

            'provider' => $provider,

            'provider_id' => $socialUser->getId(),

            'name' => $socialUser->getName()
                ?? $socialUser->getNickname()
                ?? $email,

            'email' => $email,

            'avatar' => $socialUser->getAvatar(),
        ],
    ]);


    Log::info(
        'OAuth signup information stored in session.',
        [
            'provider' => $provider,
            'email' => $email,
            'name' => $socialUser->getName(),
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Redirect New User To Role Selection Page
    |--------------------------------------------------------------------------
    |
    | New page:
    |
    | /oauth/select-role
    |
    | The page will show:
    |
    | ○ Customer
    | ○ Vendor
    |
    | [ Continue ]
    |
    */

    return redirect()->route('oauth.select-role');
}
// /**
//  * API: Google Sign-In for mobile
//  *
//  * Supports:
//  * 1. id_token - Google ID token verified by server
//  * 2. provider_id + email - direct mobile profile payload
//  *
//  * Expected JSON:
//  * {
//  *     "id_token": "<google-id-token>",
//  *     "provider_id": "google-user-id",
//  *     "name": "Pranav Mishra",
//  *     "email": "dbmtester2@gmail.com",
//  *     "photo": "https://...",
//  *     "role": "vendor"
//  * }
//  */
// public function apiGoogleLogin(Request $request)
// {
//     $request->validate([
//         'id_token'    => 'nullable|string',
//         'provider_id' => 'nullable|string',
//         'name'        => 'nullable|string',
//         'email'       => 'required|email',
//         'photo'       => 'nullable|string',
//         'role'        => 'nullable|in:customer,vendor',
//     ]);

//     $idToken    = $request->input('id_token');
//     $providerId = $request->input('provider_id');
//     $email      = strtolower(trim($request->input('email')));
//     $name       = $request->input('name');
//     $avatar     = $request->input('photo');

//     // Same default as normal registration
//     $role = $request->input('role', 'customer');

//     /*
//     |--------------------------------------------------------------------------
//     | Google ID Token Verification
//     |--------------------------------------------------------------------------
//     */
//     if ($idToken) {

//         $providerRecord = OauthProvider::where('provider', 'google')
//             ->where('active', true)
//             ->first();

//         if (!$providerRecord) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Google login is not available.',
//             ], 400);
//         }

//         try {

//             $resp = Http::asForm()->get(
//                 'https://oauth2.googleapis.com/tokeninfo',
//                 [
//                     'id_token' => $idToken,
//                 ]
//             );

//         } catch (\Throwable $e) {

//             Log::error('Google token verification failed', [
//                 'error' => $e->getMessage(),
//             ]);

//             return response()->json([
//                 'success' => false,
//                 'message' => 'Unable to verify Google token.',
//             ], 500);
//         }

//         if (!$resp->ok()) {

//             Log::warning('Invalid Google ID token', [
//                 'email' => $email,
//             ]);

//             return response()->json([
//                 'success' => false,
//                 'message' => 'Invalid id_token.',
//             ], 400);
//         }

//         $payload = $resp->json();

//         /*
//         |--------------------------------------------------------------------------
//         | Check Google Client ID
//         |--------------------------------------------------------------------------
//         */
//         if (
//             isset($payload['aud']) &&
//             !empty($providerRecord->client_id) &&
//             $payload['aud'] !== $providerRecord->client_id
//         ) {

//             Log::warning('Google ID token audience mismatch', [
//                 'aud'      => $payload['aud'],
//                 'expected' => $providerRecord->client_id,
//             ]);

//             return response()->json([
//                 'success' => false,
//                 'message' => 'Token audience mismatch.',
//             ], 400);
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Get Google User Data
//         |--------------------------------------------------------------------------
//         */
//         $email = isset($payload['email'])
//             ? strtolower(trim($payload['email']))
//             : $email;

//         $name = $payload['name'] ?? $name ?? $email;

//         $avatar = $payload['picture'] ?? $avatar;

//         $providerId = $payload['sub'] ?? $providerId;

//         $emailVerified = $payload['email_verified']
//             ?? ($payload['verified_email'] ?? false);

//         if (!$emailVerified || $emailVerified === 'false') {

//             return response()->json([
//                 'success' => false,
//                 'message' => 'Google account email is not verified.',
//             ], 400);
//         }
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | Validate Email
//     |--------------------------------------------------------------------------
//     */
//     if (!$email) {

//         return response()->json([
//             'success' => false,
//             'message' => 'Email is required.',
//         ], 400);
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | Validate Role
//     |--------------------------------------------------------------------------
//     */
//     if (!in_array($role, ['customer', 'vendor'], true)) {
//         $role = 'customer';
//     }

//     Log::info('Google mobile login request', [
//         'email' => $email,
//         'role'  => $role,
//     ]);

//     /*
//     |--------------------------------------------------------------------------
//     | Find Existing User
//     |--------------------------------------------------------------------------
//     */
//     $user = User::where('email', $email)->first();

//     /*
//     |--------------------------------------------------------------------------
//     | Existing User
//     |--------------------------------------------------------------------------
//     */
//     if ($user) {

//         /*
//         |--------------------------------------------------------------------------
//         | Check Suspension
//         |--------------------------------------------------------------------------
//         */
//         if (
//             method_exists($user, 'isSuspended') &&
//             $user->isSuspended()
//         ) {

//             return response()->json([
//                 'success' => false,
//                 'message' => 'Account suspended.',
//             ], 403);
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Update Google Profile Information
//         |--------------------------------------------------------------------------
//         */
//         $updated = [];

//         if ($name && $user->name !== $name) {
//             $updated['name'] = $name;
//         }

//         if ($avatar && $user->avatar !== $avatar) {
//             $updated['avatar'] = $avatar;
//         }

//         if (!empty($updated)) {
//             $user->update($updated);
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | IMPORTANT
//         |--------------------------------------------------------------------------
//         | Existing user's role is NOT changed automatically.
//         |
//         | We use the already assigned active_role.
//         |--------------------------------------------------------------------------
//         */
//         $user->load('roles');

//         $activeRole = $user->active_role;

//         if (!$activeRole) {
//             $activeRole = $user->getRoleNames()->first();
//         }

//         $role = $activeRole ?: 'customer';

//         Log::info('Existing Google user login', [
//             'user_id'     => $user->id,
//             'email'       => $user->email,
//             'active_role' => $user->active_role,
//             'role'        => $role,
//         ]);
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | New User
//     |--------------------------------------------------------------------------
//     */
//     else {

//         DB::beginTransaction();

//         try {

//             /*
//             |--------------------------------------------------------------------------
//             | Create User
//             |--------------------------------------------------------------------------
//             */
//             $user = User::create([
//                 'name'              => $name ?? $email,
//                 'email'             => $email,
//                 'email_verified_at' => now(),
//                 'password'          => Hash::make(Str::random(24)),
//                 'status'            => 'active',
//                 'avatar'            => $avatar,
//             ]);

//             Log::info('Google mobile user created', [
//                 'user_id' => $user->id,
//                 'email'   => $user->email,
//                 'role'    => $role,
//             ]);

//             /*
//             |--------------------------------------------------------------------------
//             | Assign Active Role
//             |--------------------------------------------------------------------------
//             |
//             | SAME AS NORMAL REGISTRATION
//             |--------------------------------------------------------------------------
//             */
//             $user->update([
//                 'active_role' => $role,
//             ]);

//             /*
//             |--------------------------------------------------------------------------
//             | Assign Spatie Role
//             |--------------------------------------------------------------------------
//             |
//             | SAME AS NORMAL REGISTRATION
//             |--------------------------------------------------------------------------
//             */
//             $user->assignRole($role);

//             Log::info('Google mobile role assigned', [
//                 'user_id'        => $user->id,
//                 'email'          => $user->email,
//                 'active_role'    => $user->active_role,
//                 'assigned_roles' => $user->getRoleNames()->toArray(),
//             ]);

//             /*
//             |--------------------------------------------------------------------------
//             | Vendor Specific Logic
//             |--------------------------------------------------------------------------
//             */
//             if ($role === 'vendor') {

//                 VendorProfile::create([
//                     'user_id'                   => $user->id,
//                     'onboarding_status'         => 'draft',
//                     'onboarding_step'           => 1,
//                     'inventory_setup_completed' => false,
//                 ]);

//                 Log::info('Vendor profile created', [
//                     'user_id' => $user->id,
//                 ]);
//             }

//             /*
//             |--------------------------------------------------------------------------
//             | Commit
//             |--------------------------------------------------------------------------
//             */
//             DB::commit();

//         } catch (\Throwable $e) {

//             DB::rollBack();

//             Log::error('Failed to create Google mobile user', [
//                 'email'   => $email,
//                 'role'    => $role,
//                 'error'   => $e->getMessage(),
//                 'file'    => $e->getFile(),
//                 'line'    => $e->getLine(),
//                 'trace'   => $e->getTraceAsString(),
//             ]);

//             return response()->json([
//                 'success' => false,
//                 'message' => 'Unable to create account.',
//                 'error'   => config('app.debug')
//                     ? $e->getMessage()
//                     : null,
//             ], 500);
//         }
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | Create Sanctum Token
//     |--------------------------------------------------------------------------
//     */
//     try {

//         /*
//         |--------------------------------------------------------------------------
//         | Use Active Role
//         |--------------------------------------------------------------------------
//         */
//         $activeRole = $user->active_role;

//         if (!$activeRole) {
//             $activeRole = $user->getRoleNames()->first();
//         }

//         $activeRole = $activeRole ?: 'customer';

//         /*
//         |--------------------------------------------------------------------------
//         | Create Token WITH ROLE ABILITY
//         |--------------------------------------------------------------------------
//         |
//         | Same pattern as normal registration:
//         | ['role:' . $role]
//         |--------------------------------------------------------------------------
//         */
//         $token = $user->createToken(
//             'auth_token',
//             ['role:' . $activeRole]
//         )->plainTextToken;

//         /*
//         |--------------------------------------------------------------------------
//         | Update Last Login
//         |--------------------------------------------------------------------------
//         */
//         if (method_exists($user, 'updateLastLogin')) {

//             $user->updateLastLogin();

//         } else {

//             $user->update([
//                 'last_login_at' => now(),
//             ]);
//         }

//     } catch (\Throwable $e) {

//         Log::error(
//             'Failed to create API token after Google login',
//             [
//                 'user_id' => $user->id ?? null,
//                 'error'   => $e->getMessage(),
//             ]
//         );

//         return response()->json([
//             'success' => false,
//             'message' => 'Unable to create access token.',
//         ], 500);
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | Reload User
//     |--------------------------------------------------------------------------
//     */
//     $user->refresh();
//     $user->load('roles');

//     $assignedRole = $user->active_role
//         ?: $user->getRoleNames()->first();

//     /*
//     |--------------------------------------------------------------------------
//     | Response
//     |--------------------------------------------------------------------------
//     */
//     return response()->json([
//         'success' => true,
//         'message' => 'Google login successful.',

//         'data' => [
//             'user' => $user,
//             'role' => $assignedRole,

//             'is_vendor' => $assignedRole === 'vendor',
//             'is_customer' => $assignedRole === 'customer',

//             'token' => $token,
//         ],
//     ], 200);
// }
/**
 * API: Google Sign-In for mobile
 *
 * Supports:
 * 1. id_token - Google ID token verified by server
 * 2. provider_id + email - direct mobile profile payload
 *
 * Expected JSON:
 * {
 *     "id_token": "<google-id-token>",
 *     "provider_id": "google-user-id",
 *     "name": "Pranav Mishra",
 *     "email": "dbmtester2@gmail.com",
 *     "photo": "https://...",
 *     "role": "vendor",
 *     "fcm_token": "fcm_token_example"
 * }
 */
public function apiGoogleLogin(Request $request)
{
    $request->validate([
        'id_token'    => 'nullable|string',
        'provider_id' => 'nullable|string',
        'name'        => 'nullable|string',
        'email'       => 'required|email',
        'photo'       => 'nullable|string',
        'role'        => 'nullable|in:customer,vendor',
        'fcm_token'   => 'nullable|string',
    ]);

    $idToken    = $request->input('id_token');
    $providerId = $request->input('provider_id');
    $email      = strtolower(trim($request->input('email')));
    $name       = $request->input('name');
    $avatar     = $request->input('photo');
    $fcmToken   = $request->input('fcm_token');

    // Same default as normal registration
    $role = $request->input('role', 'customer');

    /*
    |--------------------------------------------------------------------------
    | Google ID Token Verification
    |--------------------------------------------------------------------------
    */
    if ($idToken) {

        $providerRecord = OauthProvider::where('provider', 'google')
            ->where('active', true)
            ->first();

        if (!$providerRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Google login is not available.',
            ], 400);
        }

        try {

            $resp = Http::asForm()->get(
                'https://oauth2.googleapis.com/tokeninfo',
                [
                    'id_token' => $idToken,
                ]
            );

        } catch (\Throwable $e) {

            Log::error('Google token verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to verify Google token.',
            ], 500);
        }

        if (!$resp->ok()) {

            Log::warning('Invalid Google ID token', [
                'email' => $email,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid id_token.',
            ], 400);
        }

        $payload = $resp->json();

        /*
        |--------------------------------------------------------------------------
        | Check Google Client ID
        |--------------------------------------------------------------------------
        */
        if (
            isset($payload['aud']) &&
            !empty($providerRecord->client_id) &&
            $payload['aud'] !== $providerRecord->client_id
        ) {

            Log::warning('Google ID token audience mismatch', [
                'aud'      => $payload['aud'],
                'expected' => $providerRecord->client_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Token audience mismatch.',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Google User Data
        |--------------------------------------------------------------------------
        */
        $email = isset($payload['email'])
            ? strtolower(trim($payload['email']))
            : $email;

        $name = $payload['name'] ?? $name ?? $email;

        $avatar = $payload['picture'] ?? $avatar;

        $providerId = $payload['sub'] ?? $providerId;

        $emailVerified = $payload['email_verified']
            ?? ($payload['verified_email'] ?? false);

        if (!$emailVerified || $emailVerified === 'false') {

            return response()->json([
                'success' => false,
                'message' => 'Google account email is not verified.',
            ], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Email
    |--------------------------------------------------------------------------
    */
    if (!$email) {

        return response()->json([
            'success' => false,
            'message' => 'Email is required.',
        ], 400);
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Role
    |--------------------------------------------------------------------------
    */
    if (!in_array($role, ['customer', 'vendor'], true)) {
        $role = 'customer';
    }

    Log::info('Google mobile login request', [
        'email'     => $email,
        'role'      => $role,
        'fcm_token' => $fcmToken ? 'provided' : 'not provided',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Find Existing User
    |--------------------------------------------------------------------------
    */
    $user = User::where('email', $email)->first();

    /*
    |--------------------------------------------------------------------------
    | Existing User
    |--------------------------------------------------------------------------
    */
    if ($user) {

        /*
        |--------------------------------------------------------------------------
        | Check Suspension
        |--------------------------------------------------------------------------
        */
        if (
            method_exists($user, 'isSuspended') &&
            $user->isSuspended()
        ) {

            return response()->json([
                'success' => false,
                'message' => 'Account suspended.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Google Profile Information + FCM Token
        |--------------------------------------------------------------------------
        */
        $updated = [];

        if ($name && $user->name !== $name) {
            $updated['name'] = $name;
        }

        if ($avatar && $user->avatar !== $avatar) {
            $updated['avatar'] = $avatar;
        }

        // Update FCM token when mobile sends it
        if ($fcmToken && $user->fcm_token !== $fcmToken) {
            $updated['fcm_token'] = $fcmToken;
        }

        if (!empty($updated)) {
            $user->update($updated);
        }

        /*
        |--------------------------------------------------------------------------
        | Existing User Role
        |--------------------------------------------------------------------------
        |
        | Do NOT change existing user's role automatically.
        |--------------------------------------------------------------------------
        */
        $user->load('roles');

        $activeRole = $user->active_role;

        if (!$activeRole) {
            $activeRole = $user->getRoleNames()->first();
        }

        $role = $activeRole ?: 'customer';

        Log::info('Existing Google user login', [
            'user_id'     => $user->id,
            'email'       => $user->email,
            'active_role' => $user->active_role,
            'role'        => $role,
            'fcm_token'   => $user->fcm_token ? 'saved' : 'not available',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | New User
    |--------------------------------------------------------------------------
    */
    else {

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Create User
            |--------------------------------------------------------------------------
            */
            $user = User::create([
                'name'              => $name ?? $email,
                'email'             => $email,
                'email_verified_at' => now(),
                'password'          => Hash::make(Str::random(24)),
                'status'            => 'active',
                'avatar'            => $avatar,
                'fcm_token'         => $fcmToken,
            ]);

            Log::info('Google mobile user created', [
                'user_id'   => $user->id,
                'email'     => $user->email,
                'role'      => $role,
                'fcm_token' => $fcmToken ? 'saved' : 'not provided',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Assign Active Role
            |--------------------------------------------------------------------------
            |
            | SAME AS NORMAL REGISTRATION
            |--------------------------------------------------------------------------
            */
            $user->update([
                'active_role' => $role,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Assign Spatie Role
            |--------------------------------------------------------------------------
            |
            | SAME AS NORMAL REGISTRATION
            |--------------------------------------------------------------------------
            */
            $user->assignRole($role);

            Log::info('Google mobile role assigned', [
                'user_id'        => $user->id,
                'email'          => $user->email,
                'active_role'    => $user->active_role,
                'assigned_roles' => $user->getRoleNames()->toArray(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Vendor Specific Logic
            |--------------------------------------------------------------------------
            */
            if ($role === 'vendor') {

                VendorProfile::create([
                    'user_id'                   => $user->id,
                    'onboarding_status'         => 'draft',
                    'onboarding_step'           => 1,
                    'inventory_setup_completed' => false,
                ]);

                Log::info('Vendor profile created', [
                    'user_id' => $user->id,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */
            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('Failed to create Google mobile user', [
                'email'   => $email,
                'role'    => $role,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create account.',
                'error'   => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create Sanctum Token
    |--------------------------------------------------------------------------
    */
    try {

        $activeRole = $user->active_role;

        if (!$activeRole) {
            $activeRole = $user->getRoleNames()->first();
        }

        $activeRole = $activeRole ?: 'customer';

        /*
        |--------------------------------------------------------------------------
        | Create Token WITH ROLE ABILITY
        |--------------------------------------------------------------------------
        */
        $token = $user->createToken(
            'auth_token',
            ['role:' . $activeRole]
        )->plainTextToken;

        /*
        |--------------------------------------------------------------------------
        | Update Last Login
        |--------------------------------------------------------------------------
        */
        if (method_exists($user, 'updateLastLogin')) {

            $user->updateLastLogin();

        } else {

            $user->update([
                'last_login_at' => now(),
            ]);
        }

    } catch (\Throwable $e) {

        Log::error(
            'Failed to create API token after Google login',
            [
                'user_id' => $user->id ?? null,
                'error'   => $e->getMessage(),
            ]
        );

        return response()->json([
            'success' => false,
            'message' => 'Unable to create access token.',
        ], 500);
    }

    /*
    |--------------------------------------------------------------------------
    | Reload User
    |--------------------------------------------------------------------------
    */
    $user->refresh();
    $user->load('roles');

    $assignedRole = $user->active_role
        ?: $user->getRoleNames()->first();

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */
    return response()->json([
        'success' => true,
        'message' => 'Google login successful.',

        'data' => [
            'user'         => $user,
            'role'         => $assignedRole,
            'is_vendor'    => $assignedRole === 'vendor',
            'is_customer'  => $assignedRole === 'customer',
            'token'        => $token,
        ],
    ], 200);
}
public function showOAuthRoleSelection()
{
    /*
    |--------------------------------------------------------------------------
    | Make sure OAuth signup session exists
    |--------------------------------------------------------------------------
    */

    if (!session()->has('oauth_signup')) {

        return redirect()
            ->route('login')
            ->withErrors([
                'credentials' => 'Your Google signup session has expired. Please try again.'
            ]);
    }

    $oauthSignup = session('oauth_signup');

    return view('auth.oauth-role-selection', [
        'oauthSignup' => $oauthSignup,
    ]);
}
public function completeOAuthSignup(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | Validate Role
    |--------------------------------------------------------------------------
    */

    $request->validate([
        'role' => [
            'required',
            'in:customer,vendor',
        ],
    ]);


    /*
    |--------------------------------------------------------------------------
    | Get OAuth Data
    |--------------------------------------------------------------------------
    */

    $oauthSignup = session('oauth_signup');

    if (! $oauthSignup) {

        return redirect()
            ->route('login')
            ->withErrors([
                'credentials' =>
                    'Your Google signup session has expired. Please try again.'
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Selected Role
    |--------------------------------------------------------------------------
    */

    $role = $request->input('role');


    Log::info('OAuth role selected', [
        'email' => $oauthSignup['email'],
        'role' => $role,
    ]);


    /*
    |--------------------------------------------------------------------------
    | Double Check User
    |--------------------------------------------------------------------------
    |
    | Very important:
    | Check again before creating the account.
    |
    */

    $existingUser = User::where(
        'email',
        $oauthSignup['email']
    )->first();


    if ($existingUser) {

        Log::info('OAuth user already exists during role selection', [
            'user_id' => $existingUser->id,
            'email' => $existingUser->email,
        ]);

        session()->forget('oauth_signup');

        Auth::login($existingUser, true);

        return redirect()->intended('/');
    }


    /*
    |--------------------------------------------------------------------------
    | Create Account
    |--------------------------------------------------------------------------
    */

    DB::beginTransaction();

    try {

        /*
        |--------------------------------------------------------------------------
        | Create User
        |--------------------------------------------------------------------------
        */

        $user = User::create([

            'name' => $oauthSignup['name'],

            'email' => $oauthSignup['email'],

            /*
            | Google verified the email
            */
            'email_verified_at' => now(),

            /*
            | Random password because login is through Google
            */
            'password' => Hash::make(
                Str::random(24)
            ),

            'status' => 'active',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Assign Role
        |--------------------------------------------------------------------------
        |
        | EXACTLY SAME AS YOUR NORMAL REGISTRATION
        |
        */

        $user->assignRole($role);


        Log::info('OAuth user created and role assigned', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $role,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Vendor Setup
        |--------------------------------------------------------------------------
        */

        if ($role === 'vendor') {

            VendorProfile::create([

                'user_id' => $user->id,

                'onboarding_status' => 'draft',

                'onboarding_step' => 1,

                'inventory_setup_completed' => false,
            ]);


            Log::info('Vendor profile created for OAuth user', [
                'user_id' => $user->id,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Commit Database
        |--------------------------------------------------------------------------
        */

        DB::commit();


        /*
        |--------------------------------------------------------------------------
        | Clear OAuth Session
        |--------------------------------------------------------------------------
        */

        session()->forget('oauth_signup');


        /*
        |--------------------------------------------------------------------------
        | Login User
        |--------------------------------------------------------------------------
        */

        Auth::login($user, true);


        session([
            'merge_guest_data' => true
        ]);


        /*
        |--------------------------------------------------------------------------
        | Vendor
        |--------------------------------------------------------------------------
        */

        if ($role === 'vendor') {

            return redirect()
                ->route('vendor.onboarding.contact-details')
                ->with(
                    'success',
                    'Account created! Please complete your vendor onboarding.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Customer
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('home')
            ->with(
                'success',
                'Welcome to OohApp! Your account has been created successfully.'
            );


    } catch (\Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | Rollback
        |--------------------------------------------------------------------------
        */

        DB::rollBack();


        Log::error('OAuth account creation failed', [

            'email' => $oauthSignup['email'] ?? null,

            'role' => $role,

            'error' => $e->getMessage(),

            'exception' => get_class($e),

            'file' => $e->getFile(),

            'line' => $e->getLine(),
        ]);


        return back()
            ->withInput()
            ->with(
                'error',
                'Unable to create your account. Please try again.'
            );
    }
}
}
