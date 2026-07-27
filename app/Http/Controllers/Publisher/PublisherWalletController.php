<?php

namespace App\Http\Controllers\Publisher;

use App\Domain\Cart\Models\CartItem;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\OrderItem;
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

        $wallet = PublisherWallet::with([
            'transactions' => fn ($q) => $q->latest('created_at')->latest('id')->limit(20),
            'withdrawals' => fn ($q) => $q->latest('created_at')->latest('id')->limit(20),
        ])
        ->firstOrCreate(
            ['publisher_id' => $publisher->id],
            ['balance' => 0, 'total_earned' => 0, 'total_withdrawn' => 0]
        );

        // Kalkulasi riil royalti 70% dari seluruh penjualan bab komik yang statusnya COMPLETED
        $completedItems = OrderItem::query()
            ->whereHas('comic', fn ($q) => $q->where('publisher_id', $publisher->user_id))
            ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::COMPLETED->value))
            ->get();

        $realSalesRoyalty = (int) round($completedItems->sum('price') * 0.70);
        $realApprovedWD = (int) $wallet->withdrawals()->where('status', 'approved')->sum('amount');
        $realBalance = max(0, $realSalesRoyalty - $realApprovedWD);

        if ($wallet->balance !== $realBalance || $wallet->total_earned !== $realSalesRoyalty || $wallet->total_withdrawn !== $realApprovedWD) {
            $wallet->update([
                'balance' => $realBalance,
                'total_earned' => $realSalesRoyalty,
                'total_withdrawn' => $realApprovedWD,
            ]);
            $wallet->refresh();
        }

        $statusFilter = $request->input('status', 'all');

        $purchasesQuery = OrderItem::query()
            ->whereHas('comic', fn ($q) => $q->where('publisher_id', $publisher->user_id))
            ->with([
                'order:id,order_number,user_id,status,completed_at,created_at',
                'order.user:id,name,username,email',
                'comic:id,title,cover_image,slug',
                'chapter:id,chapter_number,title',
            ]);

        if ($statusFilter !== 'all') {
            $purchasesQuery->whereHas('order', fn ($q) => $q->where('status', $statusFilter));
        }

        $purchases = $purchasesQuery->latest('id')->paginate(15)->withQueryString();

        // Item bab komik milik publisher ini yang saat ini ada di keranjang pembaca
        $cartItems = CartItem::query()
            ->whereHas('chapter.comic', fn ($q) => $q->where('publisher_id', $publisher->user_id))
            ->with([
                'cart.user:id,name,username,email',
                'chapter.comic:id,title,cover_image,slug',
                'chapter:id,chapter_number,title',
            ])
            ->latest('created_at')
            ->get();

        $pendingWDAmount = (int) $wallet->withdrawals()->where('status', 'pending')->sum('amount');
        $availableBalance = max(0, $wallet->balance - $pendingWDAmount);

        $walletData = array_merge($wallet->toArray(), [
            'pending_withdrawal_amount' => $pendingWDAmount,
            'available_balance' => $availableBalance,
        ]);

        return Inertia::render('Publisher/Wallet/Index', [
            'publisher' => $publisher,
            'wallet' => $walletData,
            'purchases' => $purchases,
            'cartItems' => $cartItems,
            'filters' => [
                'status' => $statusFilter,
            ],
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
