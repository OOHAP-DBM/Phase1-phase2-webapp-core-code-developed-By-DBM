<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorOnboardingApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $profile = $user?->vendorProfile;

        if (
            !$user ||
            !$profile ||
            !$profile->isApproved()
        ) {
            // API request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your vendor onboarding is not approved yet.',
                ], 403);
            }

            // Web request
            return redirect()
                ->route('vendor.dashboard')
                ->with(
                    'error',
                    'Your vendor onboarding is not approved yet.'
                );
        }

        return $next($request);
    }
}