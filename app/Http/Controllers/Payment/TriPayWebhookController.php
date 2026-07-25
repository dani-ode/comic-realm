<?php

namespace App\Http\Controllers\Payment;

use App\Domain\Payment\Actions\ProcessPaymentWebhook;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TriPayWebhookController extends Controller
{
    public function handle(Request $request, ProcessPaymentWebhook $processWebhook): JsonResponse
    {
        $result = $processWebhook->execute($request);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
