<?php

namespace App\Http\Controllers\Publisher;

use App\Domain\Publisher\Actions\ApplyPublisher;
use App\Domain\Publisher\Models\PublisherProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Publisher\ApplyPublisherRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PublisherApplicationController extends Controller
{
    public function create(Request $request): InertiaResponse|RedirectResponse
    {
        $profile = PublisherProfile::where('user_id', $request->user()->id)->first();

        if ($profile && $profile->isApproved()) {
            return redirect()->route('publisher.dashboard');
        }

        return Inertia::render('Publisher/Apply', [
            'profile' => $profile,
        ]);
    }

    public function store(ApplyPublisherRequest $request, ApplyPublisher $applyPublisher): RedirectResponse
    {
        $applyPublisher->execute($request->user(), $request->toDTO());

        return redirect()->route('publisher.dashboard')->with('success', 'Pendaftaran Publisher berhasil! Selamat datang di Studio Kreator.');
    }
}
