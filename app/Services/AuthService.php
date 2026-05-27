<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService
{
    public function attemptLogin(array $credentials, Request $request): bool
    {
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return false;
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $user->last_login_at = now();
        $user->last_login_ip = $request->ip();
        $user->save();

        return true;
    }

    public function register(array $data, Request $request): User
    {
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'user_type' => $data['user_type'] ?? 'user',
        ]);

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return $user;
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function sendResetLink(array $data): string
    {
        return Password::sendResetLink($data);
    }

    public function resetPassword(array $data): string
    {
        return Password::reset($data, function ($user) use ($data) {
            $user->forceFill([
                'password'       => Hash::make($data['password']),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });
    }
}
