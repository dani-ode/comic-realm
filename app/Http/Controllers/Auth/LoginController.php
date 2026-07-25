<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class LoginController extends Controller
{
    public function create(Request $request): InertiaResponse
    {
        if (! $request->session()->has('url.intended') && $request->headers->get('referer')) {
            $referer = $request->headers->get('referer');
            if (! str_contains($referer, '/login') && ! str_contains($referer, '/register')) {
                $request->session()->put('url.intended', $referer);
            }
        }

        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $fieldType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$fieldType => $credentials['login'], 'password' => $credentials['password']], $credentials['remember'] ?? false)) {
            return redirect()->back()
                ->withErrors(['login' => 'Email/Username atau password yang Anda masukkan tidak sesuai.'])
                ->with('error', 'Email/Username atau password yang Anda masukkan tidak sesuai.');
        }

        $request->session()->regenerate();

        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
