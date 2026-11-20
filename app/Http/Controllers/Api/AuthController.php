<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return response()->json(['message' => 'Account is deactivated.'], 403);
        }

        $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);
        AuditService::log('api_login', User::class, $user->id, [], [], "API login: {$user->name}");

        $token = $user->createToken('hrms-mobile', ['*'], now()->addDays(30));

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token->plainTextToken,
            'user'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'roles'  => $user->getRoleNames(),
                'avatar' => $user->avatar_url,
                'employee' => $user->employee ? [
                    'id'          => $user->employee->id,
                    'employee_no' => $user->employee->employee_no,
                    'designation' => $user->employee->designation,
                    'department'  => $user->employee->department?->name,
                ] : null,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        AuditService::log('api_logout', User::class, $request->user()->id, [], [], "API logout: {$request->user()->name}");
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('employee.department', 'roles');
        return response()->json([
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'roles'    => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'avatar'   => $user->avatar_url,
            'employee' => $user->employee,
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        // Token refresh is handled by Sanctum expiry — re-login required
        return response()->json(['message' => 'Please login again to refresh your token.'], 401);
    }
}
