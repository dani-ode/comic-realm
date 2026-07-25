<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Payment\Models\Payment;
use App\Domain\Wallet\Models\WithdrawalRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AdminTransactionController extends Controller
{
    public function index(): InertiaResponse
    {
        $payments = Payment::with(['user', 'order'])
            ->latest()
            ->paginate(15);

        $withdrawals = WithdrawalRequest::with(['publisher'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Transactions/Index', [
            'payments' => $payments,
            'withdrawals' => $withdrawals,
        ]);
    }

    public function approveWithdrawal(int $id): RedirectResponse
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);
        $withdrawal->update([
            'status' => 'approved',
            'processed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Penarikan dana payout royalti telah disetujui & diproses.');
    }
}
