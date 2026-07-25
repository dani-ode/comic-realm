<?php

namespace App\Http\Controllers\Publisher;

use App\Domain\Publisher\Models\PublisherProfile;
use App\Domain\Wallet\Actions\RequestWithdrawal;
use App\Domain\Wallet\Models\PublisherWallet;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use InvalidArgumentException;

class PublisherWalletController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();
        $publisher = PublisherProfile::where('user_id', $user->id)->firstOrFail();

        $wallet = PublisherWallet::with(['transactions' => fn ($q) => $q->latest()->limit(20), 'withdrawals' => fn ($q) => $q->latest()->limit(10)])
            ->firstOrCreate(
                ['publisher_id' => $publisher->id],
                ['balance' => 0, 'total_earned' => 0, 'total_withdrawn' => 0]
            );

        return Inertia::render('Publisher/Wallet/Index', [
            'publisher' => $publisher,
            'wallet' => $wallet,
        ]);
    }

    public function withdraw(Request $request, RequestWithdrawal $requestWithdrawal): RedirectResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:50000'],
        ]);

        $publisher = PublisherProfile::where('user_id', $request->user()->id)->firstOrFail();

        try {
            $requestWithdrawal->execute($publisher, (int) $request->input('amount'));

            return redirect()->back()->with('success', 'Pengajuan penarikan dana payout berhasil dikirim.');
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['amount' => $e->getMessage()]);
        }
    }
}
