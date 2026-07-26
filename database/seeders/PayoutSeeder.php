<?php

namespace Database\Seeders;

use App\Domain\Publisher\Models\PublisherProfile;
use App\Domain\Wallet\Models\PublisherWallet;
use App\Domain\Wallet\Models\WalletTransaction;
use App\Domain\Wallet\Models\WithdrawalRequest;
use Illuminate\Database\Seeder;

class PayoutSeeder extends Seeder
{
    /**
     * PayoutSeeder membuat riwayat withdrawal/payout dana dan wallet transactions.
     * Aturan Bisnis Disbursement Transfer Manual:
     * - Ketika publisher mengajukan WD, status = 'pending'. Saldo dompet belum dipotong.
     * - Ketika Admin mentransfer manual & meng-approve WD, saldo dipotong,
     *   total_withdrawn bertambah, dan transaksi mutasi debit dicatat.
     */
    public function run(): void
    {
        // 1. Dani Comic Studio (Dani Pratama - Approved & Produktif)
        $daniProfile = PublisherProfile::where('brand_name', 'Dani Comic Studio')->first();
        if ($daniProfile) {
            $wallet = PublisherWallet::where('publisher_id', $daniProfile->id)->first();
            if ($wallet) {
                // Initial credit transaction (Akumulasi pendapatan royalti)
                WalletTransaction::firstOrCreate(
                    [
                        'wallet_id' => $wallet->id,
                        'reference_number' => 'EARN-DANI-001',
                    ],
                    [
                        'type' => 'credit',
                        'amount' => 12_500_000,
                        'balance_after' => 12_500_000,
                        'description' => 'Akumulasi pendapatan penjualan bab komik (Q1 & Q2)',
                        'created_at' => now()->subMonths(6),
                    ]
                );

                // WD 1 (Pengajuan Penarikan Payout Baru oleh Dani - STATUS PENDING)
                // Saldo BELUM dipotong (tetap Rp 12.500.000) sampai Admin menekan tombol Approve!
                WithdrawalRequest::firstOrCreate(
                    [
                        'wallet_id' => $wallet->id,
                        'bank_account_number' => '1234567890',
                        'amount' => 4_500_000,
                        'status' => 'pending',
                    ],
                    [
                        'publisher_id' => $daniProfile->id,
                        'bank_name' => $daniProfile->bank_name ?: 'BCA',
                        'bank_account_name' => $daniProfile->bank_account_name ?: 'Dani Pratama',
                        'status' => 'pending',
                        'created_at' => now()->subHours(5),
                    ]
                );

                // Initial Wallet State before approval:
                // total_earned: 12,500,000
                // total_withdrawn: 0
                // balance: 12,500,000 (akan berkurang menjadi 8,000,000 setelah disetujui Admin)
                $wallet->update([
                    'total_earned' => 12_500_000,
                    'total_withdrawn' => 0,
                    'balance' => 12_500_000,
                ]);
            }
        }

        // 2. Realm Art Studio (Ari Setiawan)
        $realmProfile = PublisherProfile::where('brand_name', 'Realm Art Studio')->first();
        if ($realmProfile) {
            $wallet = PublisherWallet::where('publisher_id', $realmProfile->id)->first();
            if ($wallet) {
                WalletTransaction::firstOrCreate(
                    [
                        'wallet_id' => $wallet->id,
                        'reference_number' => 'EARN-REALM-001',
                    ],
                    [
                        'type' => 'credit',
                        'amount' => 3_500_000,
                        'balance_after' => 3_500_000,
                        'description' => 'Akumulasi pendapatan penjualan bab komik',
                        'created_at' => now()->subMonths(4),
                    ]
                );

                $wdHist = WithdrawalRequest::firstOrCreate(
                    [
                        'wallet_id' => $wallet->id,
                        'bank_account_number' => '0987654321',
                        'amount' => 1_500_000,
                    ],
                    [
                        'publisher_id' => $realmProfile->id,
                        'bank_name' => $realmProfile->bank_name ?: 'Mandiri',
                        'bank_account_name' => $realmProfile->bank_account_name ?: 'Ari Setiawan',
                        'status' => 'approved',
                        'processed_at' => now()->subMonths(2),
                        'created_at' => now()->subMonths(2),
                    ]
                );

                WalletTransaction::firstOrCreate(
                    [
                        'wallet_id' => $wallet->id,
                        'reference_number' => 'WD-' . $wdHist->id,
                    ],
                    [
                        'type' => 'debit',
                        'amount' => 1_500_000,
                        'balance_after' => 2_000_000,
                        'description' => "Penarikan dana payout royalti transfer manual ke {$wdHist->bank_name} ({$wdHist->bank_account_number})",
                        'created_at' => now()->subMonths(2),
                    ]
                );

                $wallet->update([
                    'total_earned' => 3_500_000,
                    'total_withdrawn' => 1_500_000,
                    'balance' => 2_000_000,
                ]);
            }
        }

        // 3. Dark Phoenix Studio (Bimo Saputra - Blocked)
        $darkProfile = PublisherProfile::where('brand_name', 'Dark Phoenix Studio')->first();
        if ($darkProfile) {
            $wallet = PublisherWallet::where('publisher_id', $darkProfile->id)->first();
            if ($wallet) {
                WalletTransaction::firstOrCreate(
                    [
                        'wallet_id' => $wallet->id,
                        'reference_number' => 'EARN-DARK-001',
                    ],
                    [
                        'type' => 'credit',
                        'amount' => 2_000_000,
                        'balance_after' => 2_000_000,
                        'description' => 'Akumulasi pendapatan penjualan bab komik',
                        'created_at' => now()->subMonths(10),
                    ]
                );

                $wdDark = WithdrawalRequest::firstOrCreate(
                    [
                        'wallet_id' => $wallet->id,
                        'bank_account_number' => '7788990011',
                        'amount' => 1_500_000,
                    ],
                    [
                        'publisher_id' => $darkProfile->id,
                        'bank_name' => $darkProfile->bank_name ?: 'CIMB Niaga',
                        'bank_account_name' => $darkProfile->bank_account_name ?: 'Bimo Saputra',
                        'status' => 'approved',
                        'processed_at' => now()->subMonths(6),
                        'created_at' => now()->subMonths(6),
                    ]
                );

                WalletTransaction::firstOrCreate(
                    [
                        'wallet_id' => $wallet->id,
                        'reference_number' => 'WD-' . $wdDark->id,
                    ],
                    [
                        'type' => 'debit',
                        'amount' => 1_500_000,
                        'balance_after' => 500_000,
                        'description' => "Penarikan dana payout royalti transfer manual ke {$wdDark->bank_name} ({$wdDark->bank_account_number})",
                        'created_at' => now()->subMonths(6),
                    ]
                );

                $wallet->update([
                    'total_earned' => 2_000_000,
                    'total_withdrawn' => 1_500_000,
                    'balance' => 500_000,
                ]);
            }
        }
    }
}
