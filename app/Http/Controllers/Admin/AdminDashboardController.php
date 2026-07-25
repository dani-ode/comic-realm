<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Domain\Publisher\Models\PublisherProfile;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AdminDashboardController extends Controller
{
    public function index(): InertiaResponse
    {
        $totalGMV = Payment::where('status', 'PAID')->sum('amount');
        $totalOrders = Order::count();
        $totalPublishers = PublisherProfile::where('verification_status', 'approved')->count();
        $totalComics = Comic::count();
        $totalChapters = Chapter::count();

        $recentPayments = Payment::with(['order', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                'total_gmv' => (int) $totalGMV,
                'total_orders' => $totalOrders,
                'total_publishers' => $totalPublishers,
                'total_comics' => $totalComics,
                'total_chapters' => $totalChapters,
            ],
            'recentPayments' => $recentPayments,
        ]);
    }
}
