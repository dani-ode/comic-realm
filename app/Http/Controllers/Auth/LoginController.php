<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class LoginController extends Controller
{
    public function create(Request $request): InertiaResponse
    {
        $redirectParam = $request->query('redirect');

        if ($redirectParam && str_starts_with($redirectParam, '/')) {
            $request->session()->put('url.intended', $redirectParam);
        } elseif (! $request->session()->has('url.intended') && $request->headers->get('referer')) {
            $referer = $request->headers->get('referer');
            if (! str_contains($referer, '/login') && ! str_contains($referer, '/register')) {
                $request->session()->put('url.intended', $referer);
            }
        }

        return Inertia::render('Auth/Login', [
            'redirect' => $request->session()->get('url.intended'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $fieldType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        Log::info('[LoginController] Processing login request', [
            'login_input' => $credentials['login'],
            'field_type' => $fieldType,
        ]);

        if (! Auth::attempt([$fieldType => $credentials['login'], 'password' => $credentials['password']], $credentials['remember'] ?? false)) {
            Log::warning('[LoginController] Authentication failed: Invalid credentials', [
                'login_input' => $credentials['login'],
            ]);

            throw ValidationException::withMessages([
                'login' => 'Email/Username atau password yang Anda masukkan tidak sesuai.',
            ]);
        }

        $request->session()->regenerate();

        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        Log::info('[LoginController] Authentication successful', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        if (! empty($credentials['redirect']) && str_starts_with($credentials['redirect'], '/')) {
            $request->session()->put('url.intended', $credentials['redirect']);
        }

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
