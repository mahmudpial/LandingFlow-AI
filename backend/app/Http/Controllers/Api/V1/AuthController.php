<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Projects\ActivityLogService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'company_name' => $validated['company_name'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
        ]);

        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $this->activityLog->log(
            user: $user,
            action: 'auth.registered',
            description: 'Created LandingFlow AI account'
        );

        return ApiResponse::success(
            ['user' => new UserResource($user)],
            'Account registered successfully.',
            201
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = [
            'email' => strtolower(trim((string) $request->validated('email'))),
            'password' => (string) $request->validated('password'),
        ];
        $remember = (bool) $request->validated('remember', false);

        if (! Auth::guard('web')->attempt($credentials, $remember)) {
            return ApiResponse::error(
                'Invalid email address or password.',
                401,
                ['email' => ['These credentials do not match our records.']]
            );
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        return ApiResponse::success(
            ['user' => new UserResource($user)],
            'Logged in successfully.'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return ApiResponse::success(null, 'Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success(['user' => new UserResource($user)], 'Authenticated user profile.');
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $emailChanged = strtolower($validated['email']) !== strtolower($user->email);

        $user->update([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'company_name' => $validated['company_name'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'email_verified_at' => $emailChanged ? null : $user->email_verified_at,
        ]);

        $this->activityLog->log(
            user: $user,
            action: 'auth.profile_updated',
            description: 'Updated account profile details'
        );

        return ApiResponse::success(
            ['user' => new UserResource($user->fresh())],
            'Profile updated successfully.'
        );
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->update([
            'password' => $request->validated('password'),
        ]);

        $this->activityLog->log(
            user: $user,
            action: 'auth.password_updated',
            description: 'Updated account password'
        );

        return ApiResponse::success(null, 'Password updated successfully.');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::where('email', $email)->first();

        if ($user) {
            $plainToken = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => Hash::make($plainToken),
                    'created_at' => now(),
                ]
            );

            $data = ['status' => 'sent'];
            if (app()->environment(['local', 'testing'])) {
                $data['debug_reset_token'] = $plainToken;
            }

            return ApiResponse::success(
                $data,
                'If an account exists for that email, a password reset link has been generated.'
            );
        }

        return ApiResponse::success(
            ['status' => 'sent'],
            'If an account exists for that email, a password reset link has been generated.'
        );
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $email = strtolower(trim($validated['email']));
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $record || ! Hash::check($validated['token'], $record->token)) {
            return ApiResponse::error(
                'Invalid or expired password reset token.',
                422,
                ['token' => ['This password reset token is invalid or has expired.']]
            );
        }

        if ($record->created_at && now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return ApiResponse::error(
                'Password reset token has expired.',
                422,
                ['token' => ['This password reset token has expired.']]
            );
        }

        $user = User::where('email', $email)->firstOrFail();
        $user->update(['password' => $validated['password']]);
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        $this->activityLog->log(
            user: $user,
            action: 'auth.password_reset',
            description: 'Reset account password via recovery token'
        );

        return ApiResponse::success(null, 'Password has been reset successfully. You can now sign in.');
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
            $this->activityLog->log(
                user: $user,
                action: 'auth.email_verified',
                description: 'Verified account email address'
            );
        }

        return ApiResponse::success(
            ['user' => new UserResource($user->fresh())],
            'Email address verified.'
        );
    }
}
