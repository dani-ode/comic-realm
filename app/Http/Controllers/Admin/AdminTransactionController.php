<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Payment\Models\Payment;
use App\Domain\Publisher\Models\PublisherProfile;
use App\Domain\Wallet\Actions\ApproveWithdrawal;
use App\Domain\Wallet\Models\WithdrawalRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use InvalidArgumentException;

class AdminTransactionController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $publisherId = $request->query('publisher_id');
        $status = $request->query('status');
        $tab = $request->query('tab', 'payouts');

        $publishers = PublisherProfile::select('id', 'brand_name', 'user_id')
            ->orderBy('brand_name')
            ->get();

        // 1. Dana Masuk (TriPay Payments)
        $paymentsQuery = Payment::with(['user', 'order']);

        if ($publisherId) {
            $publisherProfile = PublisherProfile::find($publisherId);
            if ($publisherProfile) {
                $paymentsQuery->whereHas('order.items.comic', function ($q) use ($publisherProfile) {
                    $q->where('publisher_id', $publisherProfile->user_id);
                });
            }
        }

        $payments = $paymentsQuery->latest()->paginate(15)->withQueryString();

        // 2. Dana Keluar (Publisher Payout Requests)
        $withdrawalsQuery = WithdrawalRequest::with(['publisher', 'wallet']);

        if ($publisherId) {
            $withdrawalsQuery->where('publisher_id', $publisherId);
        }

        if ($status) {
            $withdrawalsQuery->where('status', $status);
        }

        $withdrawals = $withdrawalsQuery->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Transactions/Index', [
            'payments' => $payments,
            'withdrawals' => $withdrawals,
            'publishers' => $publishers,
            'filters' => [
                'publisher_id' => $publisherId ? (int) $publisherId : null,
                'status' => $status ?: null,
                'tab' => $tab,
            ],
        ]);
    }

    public function approveWithdrawal(int $id, ApproveWithdrawal $approveWithdrawal): RedirectResponse
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);

        try {
            $approveWithdrawal->execute($withdrawal);
            return redirect()->back()->with('success', 'Penarikan dana payout (transfer manual) telah disetujui & saldo publisher telah dipotong.');
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function rejectWithdrawal(Request $request, int $id): RedirectResponse
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);

        if ($withdrawal->status === 'approved') {
            return redirect()->back()->with('error', 'Penarikan dana yang sudah disetujui tidak dapat ditolak.');
        }

        $withdrawal->update([
            'status' => 'rejected',
            'rejection_reason' => $request->input('reason', 'Transfer manual ditolak oleh admin.'),
            'processed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Pengajuan penarikan dana publisher berhasil ditolak.');
    }
}
