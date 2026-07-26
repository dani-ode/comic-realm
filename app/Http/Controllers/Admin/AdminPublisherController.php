<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Publisher\Models\PublisherProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AdminPublisherController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = PublisherProfile::with(['user', 'wallet'])
            ->withCount('comics');

        if ($status) {
            $query->where('verification_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('brand_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $publishers = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Publishers/Index', [
            'publishers' => $publishers,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function show(int $id)
    {
        $profile = PublisherProfile::with(['user', 'wallet'])
            ->withCount('comics')
            ->findOrFail($id);

        $comics = \App\Domain\Comic\Models\Comic::where('publisher_id', $profile->user_id)
            ->withCount('chapters')
            ->latest()
            ->get();

        return response()->json([
            'publisher' => $profile,
            'comics' => $comics,
        ]);
    }

    public function approve(int $id): RedirectResponse
    {
        $profile = PublisherProfile::findOrFail($id);
        $profile->update([
            'verification_status' => 'approved',
            'approved_at' => now(),
        ]);

        $profile->user->update(['role' => 'publisher']);

        return redirect()->back()->with('success', "Studio {$profile->brand_name} telah disetujui.");
    }

    public function reject(int $id, Request $request): RedirectResponse
    {
        $request->validate(['rejection_reason' => ['nullable', 'string', 'max:500']]);

        $profile = PublisherProfile::findOrFail($id);
        $profile->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason', 'Pengajuan tidak memenuhi syarat.'),
        ]);

        return redirect()->back()->with('success', "Pengajuan Studio {$profile->brand_name} ditolak.");
    }

    public function block(int $id): RedirectResponse
    {
        $profile = PublisherProfile::findOrFail($id);
        $profile->update([
            'verification_status' => 'blocked',
        ]);

        return redirect()->back()->with('success', "Studio {$profile->brand_name} berhasil diblokir.");
    }

    public function unblock(int $id): RedirectResponse
    {
        $profile = PublisherProfile::findOrFail($id);
        $profile->update([
            'verification_status' => 'approved',
        ]);

        return redirect()->back()->with('success', "Blokir Studio {$profile->brand_name} telah dibuka.");
    }
}
