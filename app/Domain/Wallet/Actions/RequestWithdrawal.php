<?php

namespace App\Domain\Wallet\Actions;

use App\Domain\Publisher\Models\PublisherProfile;
use App\Domain\Wallet\Models\PublisherWallet;
use App\Domain\Wallet\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RequestWithdrawal
{
    public function execute(PublisherProfile $publisher, int $amount): WithdrawalRequest
    {
        return DB::transaction(function () use ($publisher, $amount) {
            $wallet = PublisherWallet::where('publisher_id', $publisher->id)->firstOrFail();

            if ($amount < 50000) {
                throw new InvalidArgumentException('Minimal penarikan dana royalti adalah Rp 50.000.');
            }

            $pendingAmount = WithdrawalRequest::where('wallet_id', $wallet->id)
                ->where('status', 'pending')
                ->sum('amount');

            $availableBalance = $wallet->balance - $pendingAmount;

            if ($availableBalance < $amount) {
                throw new InvalidArgumentException('Saldo dompet royalti yang tersedia tidak mencukupi untuk penarikan ini.');
            }

            return WithdrawalRequest::create([
                'wallet_id' => $wallet->id,
                'publisher_id' => $publisher->id,
                'amount' => $amount,
                'bank_name' => $publisher->bank_name ?: 'BCA',
                'bank_account_number' => $publisher->bank_account_number ?: '0000000000',
                'bank_account_name' => $publisher->bank_account_name ?: $publisher->brand_name,
                'status' => 'pending',
            ]);
        });
    }
}
