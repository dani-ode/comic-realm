<?php

namespace App\Domain\Wallet\Actions;

use App\Domain\Order\Models\Order;
use App\Domain\Publisher\Models\PublisherProfile;
use App\Domain\Wallet\Models\PublisherWallet;
use App\Domain\Wallet\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class CreditPublisherRoyalty
{
    /**
     * @param Order $order
     * @param float $royaltyPercentage Default 70% share for publisher
     */
    public function execute(Order $order, float $royaltyPercentage = 0.70): void
    {
        DB::transaction(function () use ($order, $royaltyPercentage) {
            $order->load(['items.comic']);

            foreach ($order->items as $item) {
                if (! $item->comic || ! $item->comic->publisher_id) {
                    continue;
                }

                $publisherProfile = PublisherProfile::where('user_id', $item->comic->publisher_id)->first();
                if (! $publisherProfile) {
                    continue;
                }

                $wallet = PublisherWallet::firstOrCreate(
                    ['publisher_id' => $publisherProfile->id],
                    ['balance' => 0, 'total_earned' => 0, 'total_withdrawn' => 0]
                );

                // Cek idempotensi: Cek apakah royalti untuk pesanan ini sudah pernah dikreditkan
                $existingTx = WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('order_id', $order->id)
                    ->first();
                if ($existingTx) {
                    continue;
                }

                $royaltyAmount = (int) round($item->price * $royaltyPercentage);
                $newBalance = $wallet->balance + $royaltyAmount;

                $wallet->update([
                    'balance' => $newBalance,
                    'total_earned' => $wallet->total_earned + $royaltyAmount,
                ]);

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'order_id' => $order->id,
                    'type' => 'credit',
                    'amount' => $royaltyAmount,
                    'balance_after' => $newBalance,
                    'description' => "Royalty 70% share for {$item->title_snapshot}",
                    'reference_number' => $order->order_number,
                ]);
            }
        });
    }
}
