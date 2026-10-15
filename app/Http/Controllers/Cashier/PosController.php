<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CashierSession;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index()
    {
        $currentSession = CashierSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if (! $currentSession) {
            return redirect()->route('cashier.session.open')->with('warning', 'Silakan buka sesi kasir terlebih dahulu sebelum bertransaksi.');
        }

        return view('cashier.pos', [
            'currentSession' => $currentSession,
        ]);
    }

    public function receipt(Order $order)
    {
        // Mitigate IDOR: Cashiers may only access receipts within their active session or user account
        if (Auth::user()->role === 'cashier') {
            $currentSession = CashierSession::where('user_id', Auth::id())->where('status', 'open')->first();
            if ($order->user_id !== Auth::id() && (!$currentSession || $order->cashier_session_id !== $currentSession->id)) {
                abort(403, 'Akses ditolak. Anda tidak berhak melihat struk pesanan ini.');
            }
        }

        $order->load(['orderItems.product', 'orderItems.variant', 'user', 'cashierSession']);

        return view('cashier.receipt', [
            'order' => $order,
        ]);
    }

    /**
     * Display order history for the cashier's current active session (SRS POS-10).
     */
    public function orders(Request $request)
    {
        $currentSession = CashierSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if (! $currentSession) {
            return redirect()->route('cashier.session.open')->with('warning', 'Silakan buka sesi kasir terlebih dahulu.');
        }

        $query = $currentSession->orders()->with(['orderItems.product', 'user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->get();

        return view('cashier.orders.index', [
            'currentSession' => $currentSession,
            'orders'         => $orders,
        ]);
    }

    /**
     * Display order history in Admin portal.
     */
    public function adminOrders(Request $request)
    {
        $query = Order::with(['orderItems.product', 'user', 'cashierSession'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->paginate(20);

        return view('admin.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function void(Request $request, Order $order)
    {
        // Authorization check for void: must be cashier who owns the order/session or admin
        if (Auth::user()->role === 'cashier') {
            $currentSession = CashierSession::where('user_id', Auth::id())->where('status', 'open')->first();
            if ($order->user_id !== Auth::id() && (!$currentSession || $order->cashier_session_id !== $currentSession->id)) {
                abort(403, 'Akses ditolak. Anda tidak berhak membatalkan pesanan ini.');
            }
        }

        try {
            $reason = $request->input('reason', 'Dibatalkan oleh kasir');
            $order->void($reason);

            return redirect()->back()->with('success', "Order #{$order->order_number} berhasil dibatalkan dan stok bahan telah dipulihkan.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
