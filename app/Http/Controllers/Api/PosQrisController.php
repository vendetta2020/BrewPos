<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderPlaced;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InventoryDeductionService;
use App\Services\QrisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosQrisController extends Controller
{
    public function __construct(
        protected QrisService $qrisService,
        protected InventoryDeductionService $inventoryService
    ) {}

    /**
     * Generate dynamic QRIS for POS order or arbitrary amount.
     * Endpoint: POST /api/pos/qris/generate
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount'          => 'nullable|numeric|min:100',
            'order_id'        => 'nullable|integer|exists:orders,id',
            'order_number'    => 'nullable|string',
            'reference_no'    => 'nullable|string',
            'merchant_name'   => 'nullable|string|max:25',
        ]);

        $orderId = $validated['order_id'] ?? null;
        $orderNumber = $validated['order_number'] ?? null;
        $amount = isset($validated['amount']) ? (float) $validated['amount'] : null;

        // If order_id or order_number is given, retrieve amount from order if not provided
        if ($orderId || $orderNumber) {
            $order = $orderId
                ? Order::find($orderId)
                : Order::where('order_number', $orderNumber)->first();

            if ($order) {
                $orderId = $order->id;
                $orderNumber = $order->order_number;
                $amount = $amount ?? (float) $order->total;
            }
        }

        if (! $amount || $amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter amount wajib diisi dan bernilai lebih dari 0.',
            ], 422);
        }

        $trx = $this->qrisService->createTransaction(
            amount: $amount,
            orderNumber: $orderNumber,
            orderId: $orderId,
            customReference: $validated['reference_no'] ?? null
        );

        // If an existing order is linked, update its payment reference
        if ($orderId) {
            Order::where('id', $orderId)->update([
                'payment_reference' => $trx['reference_no'],
                'payment_method'    => 'qris',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'QRIS generated successfully',
            'data'    => $trx,
        ], 201);
    }

    /**
     * Check QRIS payment status.
     * Endpoint: GET /api/pos/qris/{reference}/status
     */
    public function status(string $reference): JsonResponse
    {
        $trx = $this->qrisService->getTransaction($reference);

        if (! $trx) {
            // Check fallback in Order model
            $order = Order::where('payment_reference', $reference)
                ->orWhere('order_number', $reference)
                ->first();

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi QRIS tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'reference_no' => $order->payment_reference ?? $reference,
                    'order_number' => $order->order_number,
                    'amount'       => (float) $order->total,
                    'status'       => $order->status === 'paid' || $order->status === 'completed' ? 'paid' : $order->status,
                    'paid_at'      => $order->updated_at->toIso8601String(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'reference_no' => $trx['reference_no'],
                'order_number' => $trx['order_number'] ?? null,
                'amount'       => $trx['amount'],
                'status'       => $trx['status'],
                'paid_at'      => $trx['paid_at'] ?? null,
                'expires_at'   => $trx['expires_at'],
            ],
        ]);
    }

    /**
     * Webhook/Callback endpoint for payment gateway to notify BrewPOS when QRIS is paid.
     * Endpoint: POST /api/pos/qris/callback
     */
    public function callback(Request $request): JsonResponse
    {
        // Support standard payment gateway payload format
        $reference = $request->input('reference_no')
            ?? $request->input('external_id')
            ?? $request->input('order_number')
            ?? $request->input('transaction_id')
            ?? $request->input('data.payment.order_id')
            ?? $request->input('data.payment.id');
        $statusInput = $request->input('status')
            ?? $request->input('transaction_status')
            ?? $request->input('payment_status')
            ?? $request->input('data.payment.status')
            ?? 'paid';
        if (! $reference) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter reference_no atau external_id tidak ditemukan dalam payload.',
            ], 422);
        }
        // Tambahkan 'succeeded' ke dalam daftar status sukses
        $isPaid = in_array(strtolower((string) $statusInput), ['paid', 'settlement', 'success', 'succeeded', 'capture', 'completed']);

        if ($isPaid) {
            $updatedTrx = $this->qrisService->markAsPaid($reference, $request->all());

            // Check if there is an associated order in the database
            $order = Order::where('payment_reference', $reference)
                ->orWhere('order_number', $reference)
                ->first();

            if (! $order && isset($updatedTrx['order_number'])) {
                $order = Order::where('order_number', $updatedTrx['order_number'])->first();
            }

            if ($order && $order->status === 'pending') {
                DB::transaction(function () use ($order, $reference) {
                    $order->update([
                        'status'            => 'paid',
                        'payment_method'    => 'qris',
                        'payment_reference' => $reference,
                        'cash_received'     => $order->total,
                        'change_given'      => 0,
                    ]);

                    // Deduct stock for all order items if pending order was placed
                    foreach ($order->orderItems as $item) {
                        try {
                            $this->inventoryService->deductFromOrder($item);
                        } catch (\Throwable $e) {
                            // Log warning but allow order to complete
                        }
                    }
                });

                event(new OrderPlaced($order->fresh()));
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment callback processed successfully',
                'data'    => [
                    'reference_no' => $reference,
                    'status'       => 'paid',
                    'order_number' => $order?->order_number,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Callback received with status: ' . $statusInput,
            'data'    => [
                'reference_no' => $reference,
                'status'       => strtolower((string) $statusInput),
            ],
        ]);
    }

    /**
     * Helper endpoint to simulate paid status manually (useful for testing & development).
     * Endpoint: POST /api/pos/qris/simulate-pay
     */
    public function simulatePay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference_no' => 'required|string',
        ]);

        $reference = $validated['reference_no'];
        $request->merge(['status' => 'paid', 'reference_no' => $reference]);

        return $this->callback($request);
    }
}
