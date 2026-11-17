<?php

namespace App\Http\Controllers;

use App\Http\Requests\MidtransNotificationRequest;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class MidtransNotificationController extends Controller
{
    public function __invoke(MidtransNotificationRequest $request, PaymentService $payments): JsonResponse
    {
        $data = $request->validated();
        $order = $payments->verifyNotification($data);
        try {
            $payments->sync($order, 'webhook');
        } catch (RuntimeException $e) {
            return response()->json(['message' => 'Verifikasi gateway belum tersedia; ulangi notifikasi.'], 503);
        }

        return response()->json(['message' => 'Notifikasi diverifikasi.']);
    }
}
