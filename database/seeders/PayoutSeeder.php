<?php

namespace Database\Seeders;

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Domain\Publisher\Models\PublisherProfile;
use App\Domain\User\Models\User;
use App\Domain\Wallet\Models\PublisherWallet;
use App\Domain\Wallet\Models\WalletTransaction;
use App\Domain\Wallet\Models\WithdrawalRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PayoutSeeder extends Seeder
{
    /**
     * PayoutSeeder membuat riwayat transaksi pembelian bab komik dan withdrawal/payout.
     * Mengatur data yang 100% konsisten antara Order, OrderItem, Entitlement, Wallet, dan WithdrawalRequest.
     */
    public function run(): void
    {
        $readers = User::where('role', 'user')->get();
        if ($readers->isEmpty()) {
            return;
        }

        // ─────────────────────────────────────────────────────────────────────────
        // 1. Dani Comic Studio (Dani M. - publisher@comicrealm.test)
        // ─────────────────────────────────────────────────────────────────────────
        $daniProfile = PublisherProfile::where('brand_name', 'Dani Comic Studio')->first();
        if ($daniProfile) {
            $wallet = PublisherWallet::firstOrCreate(
                ['publisher_id' => $daniProfile->id],
                ['balance' => 0, 'total_earned' => 0, 'total_withdrawn' => 0]
            );

            $daniComics = Comic::where('publisher_id', $daniProfile->user_id)->get();
            $totalRoyaltyEarned = 0;
            $orderCounter = 100;

            // Generate ~20 transaksi pembelian realistis untuk bab-bab berbayar milik Dani Studio
            foreach ($daniComics as $comic) {
                $paidChapters = Chapter::where('comic_id', $comic->id)->where('is_free', false)->get();
                
                foreach ($paidChapters as $ch) {
                    foreach ($readers as $rIdx => $reader) {
                        // Tidak semua reader beli semua chapter (pola kombinasi)
                        if (($comic->id + $ch->id + $reader->id) % 3 === 0) {
                            $orderCounter++;
                            $invNum = 'INV-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
                            $createdDate = now()->subDays(rand(1, 20))->subHours(rand(1, 12));

                            $order = Order::firstOrCreate(
                                ['order_number' => $invNum],
                                [
                                    'user_id' => $reader->id,
                                    'subtotal' => $ch->price,
                                    'tax_amount' => 0,
                                    'fee_amount' => 0,
                                    'total_amount' => $ch->price,
                                    'status' => 'completed',
                                    'completed_at' => $createdDate,
                                    'created_at' => $createdDate,
                                ]
                            );

                            OrderItem::firstOrCreate(
                                ['order_id' => $order->id, 'chapter_id' => $ch->id],
                                [
                                    'comic_id' => $comic->id,
                                    'title_snapshot' => $comic->title . ' - Bab ' . $ch->chapter_number,
                                    'chapter_number_snapshot' => $ch->chapter_number,
                                    'price' => $ch->price,
                                    'created_at' => $createdDate,
                                ]
                            );

                            // Grant Entitlement
                            if (Schema::hasTable('entitlements')) {
                                DB::table('entitlements')->updateOrInsert(
                                    ['user_id' => $reader->id, 'chapter_id' => $ch->id],
                                    [
                                        'comic_id' => $comic->id,
                                        'order_id' => $order->id,
                                        'granted_at' => $createdDate,
                                        'created_at' => $createdDate,
                                        'updated_at' => $createdDate,
                                    ]
                                );
                            }

                            // Royalti 70%
                            $royalty = (int) round($ch->price * 0.70);
                            $totalRoyaltyEarned += $royalty;

                            WalletTransaction::firstOrCreate(
                                ['reference_number' => $invNum, 'wallet_id' => $wallet->id],
                                [
                                    'order_id' => $order->id,
                                    'type' => 'credit',
                                    'amount' => $royalty,
                                    'balance_after' => $totalRoyaltyEarned,
                                    'description' => "Royalty 70% share for {$comic->title} - Bab {$ch->chapter_number}",
                                    'created_at' => $createdDate,
                                ]
                            );
                        }
                    }
                }
            }

            // Tambahkan 1 transaksi pending untuk variasi
            $firstPaidCh = Chapter::whereHas('comic', fn ($q) => $q->where('publisher_id', $daniProfile->user_id))
                ->where('is_free', false)
                ->first();

            if ($firstPaidCh) {
                $pendingInv = 'INV-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
                $pendingOrder = Order::create([
                    'order_number' => $pendingInv,
                    'user_id' => $readers->first()->id,
                    'subtotal' => $firstPaidCh->price,
                    'tax_amount' => 0,
                    'fee_amount' => 0,
                    'total_amount' => $firstPaidCh->price,
                    'status' => 'pending',
                    'created_at' => now()->subHours(2),
                ]);

                OrderItem::create([
                    'order_id' => $pendingOrder->id,
                    'chapter_id' => $firstPaidCh->id,
                    'comic_id' => $firstPaidCh->comic_id,
                    'title_snapshot' => $firstPaidCh->comic->title . ' - Bab ' . $firstPaidCh->chapter_number,
                    'chapter_number_snapshot' => $firstPaidCh->chapter_number,
                    'price' => $firstPaidCh->price,
                    'created_at' => now()->subHours(2),
                ]);
            }

            $wallet->update([
                'total_earned' => $totalRoyaltyEarned,
                'total_withdrawn' => 0,
                'balance' => $totalRoyaltyEarned,
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────────
        // 2. Realm Art Studio (Ari Setiawan - realm@comicrealm.test)
        // ─────────────────────────────────────────────────────────────────────────
        $realmProfile = PublisherProfile::where('brand_name', 'Realm Art Studio')->first();
        if ($realmProfile) {
            $wallet = PublisherWallet::firstOrCreate(
                ['publisher_id' => $realmProfile->id],
                ['balance' => 0, 'total_earned' => 0, 'total_withdrawn' => 0]
            );

            $realmComics = Comic::where('publisher_id', $realmProfile->user_id)->get();
            $totalRoyaltyEarned = 0;
            $orderCounter = 200;

            foreach ($realmComics as $comic) {
                $paidChapters = Chapter::where('comic_id', $comic->id)->where('is_free', false)->get();

                foreach ($paidChapters as $ch) {
                    foreach ($readers as $reader) {
                        $orderCounter++;
                        $invNum = 'INV-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
                        $createdDate = now()->subDays(rand(10, 40))->subHours(rand(1, 12));

                        $order = Order::firstOrCreate(
                            ['order_number' => $invNum],
                            [
                                'user_id' => $reader->id,
                                'subtotal' => $ch->price,
                                'tax_amount' => 0,
                                'fee_amount' => 0,
                                'total_amount' => $ch->price,
                                'status' => 'completed',
                                'completed_at' => $createdDate,
                                'created_at' => $createdDate,
                            ]
                        );

                        OrderItem::firstOrCreate(
                            ['order_id' => $order->id, 'chapter_id' => $ch->id],
                            [
                                'comic_id' => $comic->id,
                                'title_snapshot' => $comic->title . ' - Bab ' . $ch->chapter_number,
                                'chapter_number_snapshot' => $ch->chapter_number,
                                'price' => $ch->price,
                                'created_at' => $createdDate,
                            ]
                        );

                        if (Schema::hasTable('entitlements')) {
                            DB::table('entitlements')->updateOrInsert(
                                ['user_id' => $reader->id, 'chapter_id' => $ch->id],
                                [
                                    'comic_id' => $comic->id,
                                    'order_id' => $order->id,
                                    'granted_at' => $createdDate,
                                    'created_at' => $createdDate,
                                    'updated_at' => $createdDate,
                                ]
                            );
                        }

                        $royalty = (int) round($ch->price * 0.70);
                        $totalRoyaltyEarned += $royalty;

                        WalletTransaction::firstOrCreate(
                            ['reference_number' => $invNum, 'wallet_id' => $wallet->id],
                            [
                                'order_id' => $order->id,
                                'type' => 'credit',
                                'amount' => $royalty,
                                'balance_after' => $totalRoyaltyEarned,
                                'description' => "Royalty 70% share for {$comic->title} - Bab {$ch->chapter_number}",
                                'created_at' => $createdDate,
                            ]
                        );
                    }
                }
            }

            // WD Approved 1.500.000 (Jika total royalti mencukupi)
            $wdAmount = 150000;
            if ($totalRoyaltyEarned >= $wdAmount) {
                $wdHist = WithdrawalRequest::firstOrCreate(
                    [
                        'wallet_id' => $wallet->id,
                        'bank_account_number' => $realmProfile->bank_account_number ?: '0987654321',
                        'amount' => $wdAmount,
                    ],
                    [
                        'publisher_id' => $realmProfile->id,
                        'bank_name' => $realmProfile->bank_name ?: 'Mandiri',
                        'bank_account_name' => $realmProfile->bank_account_name ?: 'Ari Setiawan',
                        'status' => 'approved',
                        'processed_at' => now()->subDays(5),
                        'created_at' => now()->subDays(7),
                    ]
                );

                WalletTransaction::firstOrCreate(
                    [
                        'wallet_id' => $wallet->id,
                        'reference_number' => 'WD-' . $wdHist->id,
                    ],
                    [
                        'type' => 'debit',
                        'amount' => $wdAmount,
                        'balance_after' => $totalRoyaltyEarned - $wdAmount,
                        'description' => "Penarikan dana payout royalti transfer manual ke {$wdHist->bank_name} ({$wdHist->bank_account_number})",
                        'created_at' => now()->subDays(5),
                    ]
                );

                $wallet->update([
                    'total_earned' => $totalRoyaltyEarned,
                    'total_withdrawn' => $wdAmount,
                    'balance' => $totalRoyaltyEarned - $wdAmount,
                ]);
            } else {
                $wallet->update([
                    'total_earned' => $totalRoyaltyEarned,
                    'total_withdrawn' => 0,
                    'balance' => $totalRoyaltyEarned,
                ]);
            }
        }
    }
}
