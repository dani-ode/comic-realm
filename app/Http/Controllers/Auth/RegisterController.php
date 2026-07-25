<?php

namespace App\Http\Controllers\Auth;

use App\Domain\User\Actions\RegisterUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;

class RegisterController extends Controller
{
    public function create(): InertiaResponse
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterRequest $request, RegisterUser $registerUser): RedirectResponse
    {
        $user = $registerUser->execute($request->toDTO());

        Auth::login($user);

        return redirect()->intended(route('home'));
    }
}
