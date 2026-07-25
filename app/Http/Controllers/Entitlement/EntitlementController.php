<?php

namespace App\Http\Controllers\Entitlement;

use App\Domain\Entitlement\Queries\UserEntitlementQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class EntitlementController extends Controller
{
    public function index(Request $request, UserEntitlementQuery $query): InertiaResponse
    {
        $entitlements = $query->getLibraryPaginator($request->user());

        return Inertia::render('Entitlement/Index', [
            'entitlements' => $entitlements,
        ]);
    }
}
