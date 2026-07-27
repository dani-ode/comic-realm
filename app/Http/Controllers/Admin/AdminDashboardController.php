<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Domain\Publisher\Models\PublisherProfile;
use App\Domain\User\Models\User;
use App\Domain\Wallet\Models\WithdrawalRequest;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AdminDashboardController extends Controller
{
    public function index(): InertiaResponse
    {
        // 1. GMV & Keuntungan Platform
        $totalGMV = (int) Order::where('status', OrderStatus::COMPLETED->value)->sum('total_amount');

        // Jika ada pembayaran TriPay lunas yang belum tercatat di order completed, gabungkan
        $tripayPaidAmount = (int) Payment::where('status', 'PAID')
            ->whereDoesntHave('order', fn ($q) => $q->where('status', OrderStatus::COMPLETED->value))
            ->sum('amount');
        
        $totalGMV += $tripayPaidAmount;

        $totalPublisherRoyalty = (int) round($totalGMV * 0.70);
        $totalPlatformRevenue = (int) round($totalGMV * 0.30);

        // 2. Withdrawal Metrics
        $pendingWithdrawalsCount = WithdrawalRequest::where('status', 'pending')->count();
        $pendingWithdrawalsAmount = (int) WithdrawalRequest::where('status', 'pending')->sum('amount');
        $approvedPayoutsAmount = (int) WithdrawalRequest::where('status', 'approved')->sum('amount');

        // 3. Studio Publisher Status
        $pendingPublishersCount = PublisherProfile::where('verification_status', 'pending')->count();
        $approvedPublishersCount = PublisherProfile::where('verification_status', 'approved')->count();

        // 4. Content & Users Totals
        $totalOrdersCount = Order::count();
        $completedOrdersCount = Order::where('status', OrderStatus::COMPLETED->value)->count();
        $totalComics = Comic::count();
        $totalChapters = Chapter::count();
        $totalUsers = User::count();

        // 5. Recent Pending Withdrawals (Memerlukan Tindakan Admin)
        $recentPendingWithdrawals = WithdrawalRequest::with('publisher')
            ->where('status', 'pending')
            ->latest('created_at')
            ->limit(5)
            ->get();

        // 6. Transaksi Penjualan Komik Terbaru
        $recentOrders = Order::with(['user', 'items.comic', 'items.chapter'])
            ->latest('created_at')
            ->limit(8)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                'total_gmv' => $totalGMV,
                'total_publisher_royalty' => $totalPublisherRoyalty,
                'total_platform_revenue' => $totalPlatformRevenue,
                'pending_withdrawals_count' => $pendingWithdrawalsCount,
                'pending_withdrawals_amount' => $pendingWithdrawalsAmount,
                'approved_payouts_amount' => $approvedPayoutsAmount,
                'pending_publishers_count' => $pendingPublishersCount,
                'approved_publishers_count' => $approvedPublishersCount,
                'total_orders' => $totalOrdersCount,
                'completed_orders_count' => $completedOrdersCount,
                'total_comics' => $totalComics,
                'total_chapters' => $totalChapters,
                'total_users' => $totalUsers,
            ],
            'recentPendingWithdrawals' => $recentPendingWithdrawals,
            'recentOrders' => $recentOrders,
        ]);
    }
}
