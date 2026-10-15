<?php

namespace App\Http\Livewire\Kds;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class KdsBoard extends Component
{
    protected $listeners = [
        'echo:orders,order-placed' => '$refresh',
        'refreshKds' => '$refresh',
    ];

    public function updateStatus(int $orderId, string $status): void
    {
        // 1. Authentication check
        if (!Auth::check()) {
            abort(401, 'Unauthenticated.');
        }

        // 2. Authorization check: only barista or admin
        if (!in_array(Auth::user()->role, ['barista', 'admin'])) {
            abort(403, 'Akses ditolak. Hanya barista atau admin yang dapat mengubah status pesanan.');
        }

        // 3. Validation: status must be an accepted order lifecycle status
        $allowedStatuses = ['pending', 'paid', 'processing', 'ready', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => "Status '{$status}' tidak valid.",
            ]);
        }

        DB::transaction(function () use ($orderId, $status) {
            $order = Order::lockForUpdate()->findOrFail($orderId);

            // Cannot update an already cancelled order
            if ($order->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => "Order #{$order->order_number} telah dibatalkan dan tidak dapat diubah statusnya.",
                ]);
            }

            // If cancelling, execute void logic and restore inventory
            if ($status === 'cancelled') {
                $order->void('Dibatalkan dari KDS');
                return;
            }

            $order->update(['status' => $status]);
        }, 3);
    }

    public function render()
    {
        // Retain active orders ('paid', 'processing', 'ready') and completed orders for 2 minutes before purging (SRS KDS-05)
        $orders = Order::whereDate('created_at', today())
            ->where(function ($query) {
                $query->whereIn('status', ['paid', 'processing', 'ready'])
                    ->orWhere(function ($q) {
                        $q->where('status', 'completed')
                          ->where('updated_at', '>=', now()->subMinutes(2));
                    });
            })
            ->with(['orderItems.product'])
            ->latest()
            ->take(25)
            ->get();

        $stats = [
            'total' => $orders->whereIn('status', ['paid', 'processing', 'ready'])->count(),
            'paid' => $orders->where('status', 'paid')->count(),
            'processing' => $orders->where('status', 'processing')->count(),
            'ready' => $orders->where('status', 'ready')->count(),
            'completed' => $orders->where('status', 'completed')->count(),
        ];

        return view('livewire.kds.kds-board', [
            'ordersList' => $orders,
            'stats' => $stats,
        ]);
    }
}
