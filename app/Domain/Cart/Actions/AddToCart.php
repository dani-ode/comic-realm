<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\DTOs\AddToCartData;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Comic\Models\Chapter;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class AddToCart
{
    public function execute(User $user, AddToCartData $data): Cart
    {
        return DB::transaction(function () use ($user, $data) {
            $chapter = Chapter::where('id', $data->chapter_id)
                ->where('status', 'published')
                ->firstOrFail();

            if ($chapter->is_free) {
                throw new InvalidArgumentException('Bab ini gratis dan tidak perlu dimasukkan ke keranjang.');
            }

            // Periksa apakah pengguna sudah memiliki hak akses baca jika tabel entitlements ada
            if (Schema::hasTable('entitlements')) {
                $hasEntitlement = DB::table('entitlements')
                    ->where('user_id', $user->id)
                    ->where('chapter_id', $chapter->id)
                    ->whereNull('revoked_at')
                    ->exists();

                if ($hasEntitlement) {
                    throw new InvalidArgumentException('Anda sudah memiliki hak akses membaca bab ini.');
                }
            }

            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['total_amount' => 0]
            );

            // Tambahkan item jika belum ada di keranjang
            CartItem::firstOrCreate(
                [
                    'cart_id' => $cart->id,
                    'chapter_id' => $chapter->id,
                ],
                [
                    'price' => $chapter->price,
                ]
            );

            $cart->recalculateTotal();

            return $cart->load(['items.chapter.comic']);
        });
    }
}
