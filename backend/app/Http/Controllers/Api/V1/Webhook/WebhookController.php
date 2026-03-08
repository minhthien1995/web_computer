<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function momo(Request $request)
    {
        Log::info('[Webhook] MoMo payload received', $request->all());

        return response()->json(['received' => true]);
    }

    public function zalopay(Request $request)
    {
        Log::info('[Webhook] ZaloPay payload received', $request->all());

        return response()->json(['received' => true]);
    }

    public function vnpay(Request $request)
    {
        Log::info('[Webhook] VNPay payload received', $request->all());

        return response()->json(['received' => true]);
    }

    public function sepay(Request $request)
    {
        Log::info('[Webhook] SePay payload received', $request->all());

        return response()->json(['received' => true]);
    }
}
