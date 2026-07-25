<?php

namespace App\Http\Middleware;

use App\Domain\Cart\Models\Cart;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? $request->user()->only(['id', 'name', 'email', 'role', 'username', 'avatar']) : null,
            ],
            'cartCount' => function () use ($request) {
                if (! $request->user()) {
                    return 0;
                }
                $cart = Cart::where('user_id', $request->user()->id)->first();

                return $cart ? $cart->items()->count() : 0;
            },
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'errors' => function () use ($request) {
                if ($request->session()->has('errors')) {
                    $bags = $request->session()->get('errors')->getBags();
                    $errors = [];
                    foreach ($bags as $bag) {
                        foreach ($bag->messages() as $key => $messages) {
                            $errors[$key] = $messages[0] ?? '';
                        }
                    }
                    return (object) $errors;
                }
                return (object) [];
            },
        ];
    }
}
