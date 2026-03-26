<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KebutuhanOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KebutuhanOrderWebhookController extends Controller
{
    /**
     * Menerima push status dari SIMPELS Backend saat pesanan dikonfirmasi/ditolak.
     * POST /api/simpels/kebutuhan-order/{orderNumber}/status
     *
     * Header wajib: X-Epos-Secret: {EPOS_WEBHOOK_SECRET}
     */
    public function updateStatus(Request $request, string $orderNumber): JsonResponse
    {
        // Validasi shared secret
        $secret = config('services.epos_webhook.secret');
        if ($secret && $request->header('X-Epos-Secret') !== $secret) {
            Log::warning('KebutuhanOrderWebhook: invalid secret', ['order' => $orderNumber]);
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'status'           => 'required|in:confirmed,rejected,expired',
            'confirmed_by'     => 'nullable|string',
            'confirmed_at'     => 'nullable|string',
            'rejection_reason' => 'nullable|string',
        ]);

        $order = KebutuhanOrder::where('order_number', $orderNumber)
            ->where('status', 'pending_confirmation')
            ->first();

        if (!$order) {
            // Mungkin sudah diupdate sebelumnya — kembalikan 200 agar SIMPELS tidak retry
            return response()->json(['success' => true, 'message' => 'Order already processed or not found']);
        }

        $mapped = match($request->status) {
            'confirmed' => 'confirmed',
            'rejected'  => 'rejected',
            'expired'   => 'expired',
        };

        $order->update([
            'status'           => $mapped,
            'confirmed_at'     => $request->confirmed_at ?? now(),
            'confirmed_by'     => $request->confirmed_by,
            'rejection_reason' => $request->rejection_reason,
        ]);

        Log::info('KebutuhanOrder status pushed from SIMPELS', [
            'order_number' => $orderNumber,
            'new_status'   => $mapped,
            'confirmed_by' => $request->confirmed_by,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Order {$orderNumber} updated to {$mapped}",
        ]);
    }
}
