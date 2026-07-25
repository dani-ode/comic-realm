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
    public function index(): InertiaResponse
    {
        $publishers = PublisherProfile::with('user')
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Publishers/Index', [
            'publishers' => $publishers,
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

        return redirect()->back()->with('success', "Publisher {$profile->brand_name} telah disetujui.");
    }

    public function reject(int $id, Request $request): RedirectResponse
    {
        $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);

        $profile = PublisherProfile::findOrFail($id);
        $profile->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        return redirect()->back()->with('success', "Pengajuan Publisher {$profile->brand_name} ditolak.");
    }
}
