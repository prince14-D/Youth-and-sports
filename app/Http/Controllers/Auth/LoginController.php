<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            AuditService::log('login_failed', null, null, [], ['email' => $request->email], 'Failed login attempt');
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Contact the administrator.',
            ]);
        }

        $request->session()->regenerate();

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        AuditService::log('login', User::class, $user->id, [], [], "User logged in: {$user->name}");

        // Check 2FA for admin roles
        if ($user->hasRole(['System Administrator', 'Human Resource Director', 'Assistant HR Director']) && $user->hasTwoFactorEnabled()) {
            session(['2fa_user_id' => $user->id, '2fa_remember' => $request->boolean('remember')]);
            Auth::logout();
            return redirect()->route('2fa.challenge');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        AuditService::log('logout', User::class, auth()->id(), [], [], "User logged out: " . auth()->user()?->name);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
