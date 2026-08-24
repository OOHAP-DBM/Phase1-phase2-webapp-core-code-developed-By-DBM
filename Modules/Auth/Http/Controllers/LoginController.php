<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SessionLog;
use App\Models\User;
use App\Services\LoggingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class LoginController extends Controller
{
    protected LoggingService $loggingService;

    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showMobileLoginForm()
    {
        return view('auth.login-mobile');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginType = filter_var(
            $credentials['login'],
            FILTER_VALIDATE_EMAIL
        ) ? 'email' : 'phone';

        $user = User::where($loginType, $credentials['login'])
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return back()
                ->withErrors([
                    'credentials' => 'The provided credentials are incorrect.',
                ])
                ->withInput();
        }

        $role = $user->getPrimaryRole();
        $routeName = $request->route()?->getName();

        \Log::info(
            'Login attempt for user with role: ' .
            $role .
            ' on route: ' .
            $routeName
        );

        if (str_starts_with($routeName ?? '', 'admin.')) {
            if (!in_array($role, ['admin', 'superadmin', 'super_admin'])) {
                return back()
                    ->withErrors([
                        'credentials' => 'Only administrators can login here.',
                    ])
                    ->withInput();
            }
        }

        if (str_starts_with($routeName ?? '', 'login')) {
            if (in_array($role, ['admin', 'superadmin', 'super_admin'])) {
                return back()
                    ->withErrors([
                        'credentials' => 'Administrators must login from the admin portal.',
                    ])
                    ->withInput();
            }
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors([
                    'credentials' => 'The provided credentials are incorrect.',
                ])
                ->withInput();
        }

        if ($user->isSuspended()) {
            return back()
                ->withErrors([
                    'account_status' => 'Account suspended. Please contact support.',
                ])
                ->withInput();
        }

        if (
            $user->status === 'pending_verification' &&
            !$user->isVendor()
        ) {
            return back()
                ->withErrors([
                    'account_status' => 'Account is pending verification. Please check your email.',
                ])
                ->withInput();
        }

        if ($user->status !== 'active') {
            return back()
                ->withErrors([
                    'account_status' => 'Your account is not active.',
                ])
                ->withInput();
        }

        Auth::login(
            $user,
            $request->boolean('remember')
        );

        session([
            'merge_guest_data' => true,
        ]);

        $user->updateLastLogin();

        $request->session()->regenerate();

        $this->loggingService->login([
            'auth_method' => 'web',
            'role' => $role,
            'identifier' => $credentials['login'],
            'remember' => $request->boolean('remember'),
        ]);

        \Log::info(
            'Web login successful for user: ' . $user->id
        );

        return $this->redirectBasedOnRole($user);
    }

    protected function redirectBasedOnRole(User $user)
    {
        $role = $user->getPrimaryRole();

        switch ($role) {
            case 'customer':
                return redirect()
                    ->intended(route('home'));

            case 'vendor':
                return $this->handleVendorRedirect($user);

            case 'admin':
                return redirect()
                    ->intended(route('admin.dashboard'));

            default:
                Auth::logout();

                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Invalid user role. Please contact support.'
                    );
        }
    }

    protected function handleVendorRedirect(User $user)
    {
        $vendorProfile = $user->vendorProfile;

        if (!$vendorProfile) {
            return redirect()
                ->route('vendor.onboarding.contact-details')
                ->with(
                    'info',
                    'Please complete your vendor onboarding.'
                );
        }

        switch ($vendorProfile->onboarding_status) {
            case 'draft':
                return $this->redirectToOnboardingStep(
                    $vendorProfile->onboarding_step
                );

            case 'pending_approval':
                return redirect()
                    ->route('vendor.dashboard')
                    ->with(
                        'info',
                        'Your application is under review. We will notify you once approved.'
                    );

            case 'approved':
                return redirect()
                    ->intended(route('vendor.dashboard'));

            case 'rejected':
                return redirect()
                    ->route('vendor.onboarding.rejected')
                    ->with(
                        'error',
                        'Your vendor application was rejected. Please contact support.'
                    );

            case 'suspended':
                Auth::logout();

                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Your vendor account has been suspended. Please contact support.'
                    );

            default:
                return redirect()
                    ->route('vendor.onboarding.contact-details');
        }
    }

    protected function redirectToOnboardingStep(int $step)
    {
        $routes = [
            1 => 'vendor.onboarding.contact-details',
            2 => 'vendor.onboarding.business-info',
            3 => 'vendor.onboarding.kyc-documents',
            4 => 'vendor.onboarding.bank-details',
            5 => 'vendor.onboarding.terms-agreement',
        ];

        $route = $routes[$step]
            ?? 'vendor.onboarding.contact-details';

        return redirect()
            ->route($route)
            ->with(
                'info',
                'Please complete your vendor onboarding.'
            );
    }

    public function logout(Request $request)
    {
        $loggedOutAt = now();

        if (!Auth::check()) {
            return redirect()
                ->route('login')
                ->with('error', 'You are already logged out.');
        }

        $user = Auth::user();

        /*
         * Update existing session log BEFORE destroying
         * the authenticated session.
         */
        try {
            $sessionLog = \App\Models\SessionLog::activeForUser($user->id);

            if ($sessionLog) {
                $sessionLog->end();

                \Log::info('Session log completed successfully', [
                    'user_id' => $user->id,
                    'session_log_id' => $sessionLog->id,
                    'logout_at' => $loggedOutAt->toDateTimeString(),
                ]);
            } else {
                \Log::warning('No active session log found during logout', [
                    'user_id' => $user->id,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('Logout session logging failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        /*
         * Clear remember token
         */
        $user->setRememberToken(null);
        $user->save();

        /*
         * Logout authenticated user
         */
        Auth::logout();

        /*
         * Invalidate session
         */
        $request->session()->invalidate();

        /*
         * Regenerate CSRF token
         */
        $request->session()->regenerateToken();

        /*
         * Remove remember-me cookie
         */
        $cookieName = Auth::getRecallerName();

        return redirect()
            ->route('login')
            ->with(
                'success',
                'You have been logged out successfully at ' .
                $loggedOutAt->format('d/m/Y H:i:s')
            )
            ->with(
                'logout_time',
                $loggedOutAt->toISOString()
            )
            ->withoutCookie($cookieName);
    }
}