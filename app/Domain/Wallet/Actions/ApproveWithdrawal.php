<?php

namespace App\Domain\Wallet\Actions;

use App\Domain\Wallet\Models\PublisherWallet;
use App\Domain\Wallet\Models\WalletTransaction;
use App\Domain\Wallet\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ApproveWithdrawal
{
    public function execute(WithdrawalRequest $withdrawal): WithdrawalRequest
    {
        return DB::transaction(function () use ($withdrawal) {
            if ($withdrawal->status === 'approved') {
                throw new InvalidArgumentException('Penarikan dana ini sudah disetujui sebelumnya.');
            }

            $wallet = PublisherWallet::where('id', $withdrawal->wallet_id)
                ->orWhere('publisher_id', $withdrawal->publisher_id)
                ->firstOrFail();

            if ($wallet->balance < $withdrawal->amount) {
                throw new InvalidArgumentException('Saldo dompet publisher tidak mencukupi untuk memproses penarikan ini.');
            }

            $newBalance = $wallet->balance - $withdrawal->amount;

            $wallet->update([
                'balance' => $newBalance,
                'total_withdrawn' => $wallet->total_withdrawn + $withdrawal->amount,
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $withdrawal->amount,
                'balance_after' => $newBalance,
                'description' => "Penarikan dana payout royalti transfer manual ke {$withdrawal->bank_name} ({$withdrawal->bank_account_number})",
                'reference_number' => 'WD-' . $withdrawal->id,
            ]);

            $withdrawal->update([
                'status' => 'approved',
                'processed_at' => now(),
            ]);

            return $withdrawal;
        });
    }
}
