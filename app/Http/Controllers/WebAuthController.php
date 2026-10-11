<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class WebAuthController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function requestPasswordReset()
    {
        return view('auth.forgot-password');
    }

    public function sendPasswordResetLink(ForgotPasswordRequest $request)
    {
        Password::sendResetLink($request->validated());

        return back()->with(
            'status',
            'Jika alamat email terdaftar, tautan untuk mengatur ulang kata sandi akan dikirim.'
        );
    }

    public function showPasswordResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->validated(),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', 'Kata sandi berhasil diatur ulang. Silakan masuk.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa.']);
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}