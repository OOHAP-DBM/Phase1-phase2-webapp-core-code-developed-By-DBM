<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     title="OOHApp API",
 *     version="1.0.0",
 *     description="API documentation for OOHApp"
 * )
 */

/**
 * Google API Login (Mobile payload)
 *
 * OpenAPI annotations for App\Http\Controllers\OAuthController::apiGoogleLogin
 */
class ApiGoogleAuthMobile
{
    /**
     * @OA\Post(
     *     path="/auth/google",
     *     summary="Google Sign-In (API) — mobile-friendly",
     *     tags={"Auth"},
     *     description="Accepts either a Google id_token (preferred) or a trusted mobile payload with provider_id, name, email and photo. Email is required. If the email exists, the user is logged in; otherwise a new account is created (role defaults to customer).",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="id_token", type="string", description="(Optional) Google ID token returned by client-side Google Sign-In. When present the server verifies it with Google and overrides other fields.", example="eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9..."),
     *                 @OA\Property(property="provider_id", type="string", description="(Optional) Google provider UID (sub). Useful when id_token is not sent.", example="fgQiPtBNyjT426FnqAR9xMUDnhw2"),
     *                 @OA\Property(property="name", type="string", description="User's display name from Google", example="Pranav Mishra"),
     *                 @OA\Property(property="email", type="string", format="email", description="User email (required)", example="dbmtester2@gmail.com"),
     *                 @OA\Property(property="photo", type="string", description="Profile photo URL from Google", example="https://lh3.googleusercontent.com/a/ACg8ocK..."),
     *                 @OA\Property(property="role", type="string", description="Optional role to assign when creating a new account (customer|vendor). Defaults to customer.", example="vendor", enum={"customer","vendor"}),
     *                 required={"email"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login — returns a Sanctum token and user object",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="access_token", type="string", example="1|mQh..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=123),
     *                 @OA\Property(property="name", type="string", example="Pranav Mishra"),
     *                 @OA\Property(property="email", type="string", example="dbmtester2@gmail.com"),
     *                 @OA\Property(property="avatar", type="string", example="https://lh3.googleusercontent.com/a/ACg8ocK..."),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"vendor"})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (missing email or invalid token)",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Email is required."))
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (account suspended)",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Account suspended."))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Unable to verify token."))
     *     )
     * )
     */
    public function post() {}
}
