<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Notifications\AdminUserRegisteredNotification;
use App\Notifications\UserWelcomeNotification;
use App\Notifications\VendorApprovalPendingNotification;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;



class RegisterController extends Controller
{
    /**
     * Show role selection screen (first step)
     */
    public function showRoleSelection()
    {
        return view('auth.role-selection');
    }

    /**
     * Store selected role in session
     */
    public function storeRoleSelection(Request $request)
    {
        $request->validate([
            'role' => 'required|in:customer,vendor',
        ]);

        // Store role in session
        session(['signup_role' => $request->role]);

        ActivityLog::record(
            action: 'registration_role_selected',
            description: 'User selected registration role: ' . $request->role,
            module: 'registration',
            metadata: [
                'role' => $request->role,
            ]
        );

        return redirect()->route('register.form');
    }

    /**
     * Show registration form (second step)
     */
    public function showRegistrationForm()
    {
        // Ensure role is selected
        if (!session()->has('signup_role')) {
            return redirect()->route('register.role-selection')
                ->with('error', 'Please select your role first.');
        }

        $role = session('signup_role');

        return view('auth.register', compact('role'));
    }

    public function showMobileForm(Request $request)
    {
        if (!session()->has('signup_role')) {
            return redirect()->route('register.role-selection')
                ->with('error', 'Please select role first');
        }
        $role = session('signup_role');
        return view('auth.register-mobile', compact('role'));
    }



    /**
     * Handle registration
     */
    public function register(RegisterRequest $request)
    {

        // Restore role from request if session is missing (for multi-step forms)
        if (!session()->has('signup_role') && $request->filled('role')) {
            session(['signup_role' => $request->input('role')]);
        }
        if (!session()->has('signup_role')) {
            return redirect()->route('register.role-selection')
                ->with('error', 'Please select your role first.');
        }
        $role = session('signup_role');

        DB::beginTransaction();

        try {
            \Log::debug('RegisterController@register: request', $request->all());
            // Create user
            $user = User::create([
                'name' => $request->name,

                'email' => $request->email,
                'email_verified_at' => $request->email_verified ? now() : null,

                'phone' => $request->phone,
                'phone_verified_at' => $request->phone_verified ? now() : null,

                'password' => Hash::make($request->password),

                'status' => 'active',
            ]);
            app(\App\Services\LoggingService::class)->created(
                $user,
                'registration',
                'User account created through registration.',
                [
                    'registration_method' => 'web',
                    'registration_role' => $role,
                ]
            );


            // Assign role
            $user->assignRole($role);

            // Send welcome email (queued - better performance)
            try {
                if (!empty($user->email)) {
                    Mail::to($user->email)->send(
                        $role === 'vendor'
                        ? new \Modules\Mail\VendorWelcomeMail($user)
                        : new \Modules\Mail\CustomerWelcomeMail($user)
                    );
                }
            } catch (\Throwable $e) {
                \Log::error('Welcome mail failed', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
            if ($role === 'customer') {


                $admins = User::role('admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(
                        new AdminUserRegisteredNotification($user, 'customer')
                    );
                }


                $user->notify(
                    new UserWelcomeNotification('customer')
                );
            }


            // Handle vendor-specific setup
            // Handle vendor-specific setup
            if ($role === 'vendor') {

                \Log::info('========== VENDOR AUTO APPROVAL DEBUG START ==========', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'role' => $role,
                ]);

                $autoApproval = \App\Models\Setting::get(
                    'auto_vendor_approval',
                    false
                );

                \Log::info('VENDOR AUTO APPROVAL: Setting fetched', [
                    'setting_key' => 'auto_vendor_approval',
                    'value' => $autoApproval,
                    'type' => gettype($autoApproval),
                    'boolean_value' => (bool) $autoApproval,
                ]);


                $onboardingStatus = $autoApproval
                    ? 'approved'
                    : 'pending_approval';

                \Log::info('VENDOR AUTO APPROVAL: Status determined', [
                    'auto_approval' => (bool) $autoApproval,
                    'onboarding_status' => $onboardingStatus,
                ]);





                $vendorProfile = VendorProfile::create([

                    'user_id' => $user->id,

                    'onboarding_status' => $onboardingStatus,

                    'onboarding_step' => 1,

                    'inventory_setup_completed' => false,

                    'approved_at' => $autoApproval ? now() : null,

                    // NULL because this is system auto-approval.
                    // Admin approval will set auth()->id().
                    'approved_by' => null,
                ]);

                app(\App\Services\LoggingService::class)->statusChanged(
                    $vendorProfile,
                    'pending_approval',
                    'approved',
                    'vendor_approval',
                    'Vendor account automatically approved by system.'
                );

                $activity = ActivityLog::record(
                    action: 'vendor_registered',
                    description: 'New vendor account registered successfully.',
                    module: 'registration',
                    subject: $user,
                    metadata: [
                        'vendor_profile_id' => $vendorProfile->id,
                        'onboarding_status' => $onboardingStatus,
                        'auto_approved' => (bool) $autoApproval,
                    ]
                );

                \Log::info('VENDOR ACTIVITY LOG RESULT', [
                    'result' => $activity?->id,
                    'user_id' => $user->id,
                    'vendor_profile_id' => $vendorProfile->id,
                ]);




                $savedProfile = VendorProfile::find($vendorProfile->id);

                \Log::info('VENDOR AUTO APPROVAL: Database verification', [
                    'profile_id' => $savedProfile?->id,
                    'db_status' => $savedProfile?->onboarding_status,
                    'db_approved_at' => $savedProfile?->approved_at,
                    'db_approved_by' => $savedProfile?->approved_by,
                ]);

                if (!$autoApproval) {

                    \Log::warning('VENDOR AUTO APPROVAL: Auto approval is OFF', [
                        'user_id' => $user->id,
                        'profile_id' => $vendorProfile->id,
                    ]);

                    ActivityLog::record(
                        action: 'vendor_pending_approval',
                        description: 'Vendor registration submitted and is pending admin approval.',
                        module: 'vendor_approval',
                        subject: $vendorProfile,
                        metadata: [
                            'user_id' => $user->id,
                            'status' => 'pending_approval',
                        ]
                    );


                    $admins = User::role('admin')->get();

                    \Log::info('VENDOR AUTO APPROVAL: Sending pending notification', [
                        'admin_count' => $admins->count(),
                        'vendor_id' => $user->id,
                    ]);

                    foreach ($admins as $admin) {
                        $admin->notify(
                            new VendorApprovalPendingNotification($user)
                        );
                    }


                    $user->notify(
                        new VendorApprovalPendingNotification($user)
                    );

                } else {

                    \Log::info('VENDOR AUTO APPROVAL: Vendor AUTO APPROVED', [
                        'user_id' => $user->id,
                        'profile_id' => $vendorProfile->id,
                        'status' => $vendorProfile->onboarding_status,
                    ]);

                    ActivityLog::record(
                        action: 'vendor_auto_approved',
                        description: 'Vendor account was automatically approved.',
                        module: 'vendor_approval',
                        subject: $vendorProfile,
                        metadata: [
                            'approved_by' => 'system',
                            'user_id' => $user->id,
                        ]
                    );
                }

                DB::commit();

                \Log::info('VENDOR AUTO APPROVAL: Transaction committed', [
                    'user_id' => $user->id,
                    'profile_id' => $vendorProfile->id,
                    'final_status' => $vendorProfile->fresh()->onboarding_status,
                ]);

                \Log::info('========== VENDOR AUTO APPROVAL DEBUG END ==========');


                session()->forget('signup_role');


                Auth::login($user);
                ActivityLog::record(
                    action: 'customer_registered',
                    description: 'New customer account registered successfully.',
                    module: 'registration',
                    subject: $user,
                    metadata: [
                        'registration_method' => 'web',
                    ]
                );

                session(['merge_guest_data' => true]);


                return redirect()
                    ->route('vendor.onboarding.contact-details')
                    ->with(
                        'success',
                        $autoApproval
                        ? 'Account created successfully! Your vendor account has been approved.'
                        : 'Account created! Please wait for admin approval.'
                    );
            }


            // Customer flow    
            DB::commit();

            // Clear session role
            session()->forget('signup_role');

            // Login the customer
            Auth::login($user);
            session(['merge_guest_data' => true]);

            // Redirect to customer dashboard
            return redirect()->route('home')
                ->with('success', 'Welcome to OohApp! Your account has been created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Registration failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Registration failed. Please try again.');
        }
    }

    /**
     * Resend email verification
     */
    public function resendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('customer.dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent to your email!');
    }

    public function sendEmailOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already registered. Please login instead.',
            ], 422);
        }
        $otp = rand(1000, 9999);
        // $otp = 1234;

        Cache::put('email_otp_' . $request->email, $otp, now()->addMinutes(1));

        try {
            Mail::to($request->email)->send(new \Modules\Mail\OtpVerificationMail($otp));
        } catch (\Exception $e) {
            \Log::error('Email OTP send failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send OTP. Please try again.']);
        }
        return response()->json(['success' => true]);
    }

    public function verifyEmailOtp(Request $request)
    {
        $request->validate(['email' => 'required|email', 'otp' => 'required']);
        $cachedOtp = Cache::get('email_otp_' . $request->email);
        if ($cachedOtp && $request->otp == $cachedOtp) {
            Cache::forget('email_otp_' . $request->email);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Invalid OTP']);
    }

    public function sendPhoneOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10'
        ]);

        if (User::where('phone', $request->phone)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This mobile number is already registered. Please login.',
            ], 422);
        }

        $otp = rand(1000, 9999);

        // Store OTP for 1 minute
        Cache::put(
            'phone_otp_' . $request->phone,
            $otp,
            now()->addMinutes(1)
        );

        try {

            // Check Twilio credentials
            if (
                empty(env('TWILIO_SID')) ||
                empty(env('TWILIO_TOKEN')) ||
                empty(env('TWILIO_FROM'))
            ) {

                Log::warning('Twilio credentials not found.');


                Log::info("Phone OTP for {$request->phone}: {$otp}");

                return response()->json([
                    'success' => true,
                    'message' => 'OTP generated successfully (Development Mode).',

                ]);
            }

            $client = new Client(
                env('TWILIO_SID'),
                env('TWILIO_TOKEN')
            );

            $client->messages->create(
                '+91' . $request->phone,
                [
                    'from' => env('TWILIO_FROM'),
                    'body' => "Your OOHAPP OTP is {$otp}. Valid for 1 minute."
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully.'
            ]);

        } catch (\Throwable $e) {

            Log::error('Twilio OTP Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Fallback for development
            Log::info("Phone OTP for {$request->phone}: {$otp}");

            return response()->json([
                'success' => true,
                'message' => 'Twilio failed. OTP generated for development.',
                'otp' => $otp // Remove this in production
            ]);
        }
    }


    public function verifyPhoneOtp(Request $request)
    {
        $request->validate(['phone' => 'required', 'otp' => 'required']);
        $cachedOtp = Cache::get('phone_otp_' . $request->phone);
        if ($cachedOtp && $request->otp == $cachedOtp) {
            Cache::forget('phone_otp_' . $request->phone);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Invalid OTP']);
    }

    public function skipContactVerification(): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->vendorProfile;

        if (!$profile) {
            return response()->json(['message' => 'Vendor profile not found'], 404);
        }
        $profile->update(['onboarding_step' => 2]);
        ActivityLog::record(
            action: 'contact_verification_skipped',
            description: 'Vendor skipped contact verification during onboarding.',
            module: 'vendor_onboarding',
            subject: $profile,
            metadata: [
                'onboarding_step' => 2,
            ]
        );
        return response()->json(['message' => 'Contact verification skipped']);
    }
}
