<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Modules\Auth\Services\OTPService;
use App\Services\ProfileService;
use Illuminate\Validation\Rule;
use Throwable;
use App\Models\User;



class ProfileController extends Controller
{
    public function show(Request $request, ProfileService $service)
    {
        return response()->json([
            'success' => true,
            'data' => $service->response($request->user(), [
                'company_name' => $request->user()->company_name,
                'gstin' => $request->user()->gstin,
            ]),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/profile/customer/completion",
     *     tags={"Customer Profile"},
     *     summary="Get customer profile completion percentage",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Customer profile completion status",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="type", type="string", example="customer"),
     *                 @OA\Property(property="percentage", type="integer", example=67),
     *                 @OA\Property(property="filled", type="integer", example=4),
     *                 @OA\Property(property="total", type="integer", example=6),
     *                 @OA\Property(property="is_complete", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function completion(Request $request)
    {

        $user = $request->user();
        if ($user->active_role === 'vendor') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            $fields = [
            $user->name,
            $user->email,
            $user->phone,
            $user->avatar,
            $user->company_name,
            $user->gstin,
        ];

        $filled = count(array_filter($fields, fn ($value) => !is_null($value) && $value !== ''));
        $total = count($fields);
        $percentage = $total > 0 ? round(($filled / $total) * 100) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'type' => 'customer',
                'percentage' => $percentage,
                'filled' => $filled,
                'total' => $total,
                'is_complete' => $percentage >= 100,
            ],
        ]);
    }

    public function update(Request $request, ProfileService $service)
    {
        $user = $request->user();

        if (!$user->email_verified_at && !$user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Verify either email or phone first',
            ], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',

            'gstin' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('users', 'gstin')->ignore($user->id),
            ],

            'avatar' => 'nullable|image|max:2048',
        ]);

        // Empty GSTIN ko NULL bana do
        $data['gstin'] = filled($request->gstin)
            ? strtoupper(trim($request->gstin))
            : null;

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $service->updateAvatar($user, $request->file('avatar'));
        }

        $user->update($data);

        send(
            $user,
            'Profile Updated Successfully',
            'Your profile details have been updated successfully.',
            [
                'type' => 'profile_update'
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile updated',
            'data' => $service->response($user->fresh(), [
                'company_name' => $user->company_name,
                'gstin' => $user->gstin,
            ]),
        ]);
    }

    public function removeAvatar(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->update(['avatar' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar removed successfully',
            ]);
        } catch (Throwable $e) {
            Log::error('AVATAR_REMOVE_FAILED', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to remove avatar',
            ], 500);
        }
    }

    public function changePassword(Request $request, ProfileService $service)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => [
                    'required',
                    'confirmed'
                ],
            ]);

            $result = $service->changePassword(
                $request->user(),
                $request->current_password,
                $request->new_password
            );

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
            ], $result['success'] ? 200 : 422);
        } catch (\Throwable $e) {
            \Log::error('PASSWORD_CHANGE_FAILED', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to change password',
            ], 500);
        }
    }
    public function sendOtp(Request $request, ProfileService $service)
    {
        $data = $request->validate([
            'type' => 'required|in:email,phone',
            'value' => 'required|string',
        ]);

        $user = $request->user();

        if ($data['type'] === 'email') {
            if ($data['value'] === $user->email) {
                return response()->json(['success' => false, 'message' => 'Same email'], 422);
            }
            if (User::where('email', $data['value'])->exists()) {
                return response()->json(['success' => false, 'message' => 'Email already taken'], 422);
            }
        }

        if ($data['type'] === 'phone') {
            if ($data['value'] === $user->phone) {
                return response()->json(['success' => false, 'message' => 'Same phone'], 422);
            }
            if (User::where('phone', $data['value'])->exists()) {
                return response()->json(['success' => false, 'message' => 'Phone already taken'], 422);
            }
        }

        $service->send($user, $data['type'], $data['value']);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
        ]);
    }

    public function verifyOtp(Request $request, ProfileService $service)
    {
        $data = $request->validate([
            'type' => 'required|in:email,phone',
            'value' => 'required|string',
            'otp' => 'required|digits:4',
        ]);

        $service->verify(
            $request->user(),
            $data['type'],
            $data['value'],
            $data['otp']
        );

        return response()->json([
            'success' => true,
            'message' => ucfirst($data['type']) . ' updated successfully',
        ]);
    }
}
