<?php

namespace App\Http\Middleware;

use App\Domain\Cart\Models\Cart;
use App\Domain\Comic\Models\Genre;
use App\Domain\Engagement\Models\Bookmark;
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
                'user' => fn () => $request->user() ? $request->user()->only(['id', 'name', 'email', 'role', 'username', 'avatar']) : null,
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
            'bookmarkedComicIds' => function () use ($request) {
                if (! $request->user()) {
                    return [];
                }

                return Bookmark::where('user_id', $request->user()->id)
                    ->pluck('comic_id')
                    ->toArray();
            },
            'topGenres' => fn () => Genre::where('is_active', true)
                ->withCount('comics')
                ->orderByDesc('comics_count')
                ->limit(7)
                ->get(['id', 'name', 'slug']),
            'cartChapterIds' => function () use ($request) {
                if (! $request->user()) {
                    return [];
                }

                $cart = Cart::where('user_id', $request->user()->id)->first();

                return $cart ? $cart->items()->pluck('chapter_id')->toArray() : [];
            },
            'pendingOrderChapterIds' => function () use ($request) {
                if (! $request->user()) {
                    return [];
                }

                return \Illuminate\Support\Facades\DB::table('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.user_id', $request->user()->id)
                    ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(orders.status)'), ['pending', 'unpaid'])
                    ->where(function ($q) {
                        $q->whereNull('orders.expired_at')->orWhere('orders.expired_at', '>', now());
                    })
                    ->pluck('order_items.chapter_id')
                    ->toArray();
            },
        ];
    }
}
