<?php

namespace App\Http\Controllers\Publisher;

use App\Domain\Publisher\Actions\ApplyPublisher;
use App\Domain\Publisher\Models\PublisherProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Publisher\ApplyPublisherRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PublisherApplicationController extends Controller
{
    public function create(Request $request): InertiaResponse|RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $profile = PublisherProfile::where('user_id', $request->user()->id)->first();

        // Jika sudah mendaftar studio (baik status pending maupun approved), langsung redirect ke dashboard
        if ($profile) {
            return redirect()->route('publisher.dashboard');
        }

        return Inertia::render('Publisher/Apply', [
            'profile' => $profile,
        ]);
    }

    public function store(ApplyPublisherRequest $request, ApplyPublisher $applyPublisher): RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $profile = PublisherProfile::where('user_id', $request->user()->id)->first();
        if ($profile) {
            return redirect()->route('publisher.dashboard');
        }

        $applyPublisher->execute($request->user(), $request->toDTO());

        return redirect()->route('publisher.dashboard')->with('success', 'Pendaftaran Publisher berhasil! Pengajuan Anda sedang dalam proses verifikasi admin.');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'brand_name' => ['required', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_name' => ['nullable', 'string', 'max:150'],
        ]);

        $profile = PublisherProfile::where('user_id', $request->user()->id)->firstOrFail();

        $profile->update([
            'brand_name' => $request->input('brand_name'),
            'slug' => Str::slug($request->input('brand_name')),
            'bio' => $request->input('bio'),
            'bank_name' => $request->input('bank_name'),
            'bank_account_number' => $request->input('bank_account_number'),
            'bank_account_name' => $request->input('bank_account_name'),
            'verification_status' => 'pending', // Dikembalikan ke pending untuk verifikasi ulang
            'rejection_reason' => null,
        ]);

        return redirect()->route('publisher.dashboard')->with('success', 'Perubahan profil studio berhasil disimpan dan dikirim ulang untuk verifikasi admin.');
    }
}
