<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CashierSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierSessionController extends Controller
{
    public function openForm()
    {
        $activeSession = CashierSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($activeSession) {
            return redirect()->route('pos')->with('info', 'Anda sudah memiliki sesi kasir yang aktif.');
        }

        return view('cashier.session.open');
    }

    public function storeSession(Request $request)
    {
        $activeSession = CashierSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($activeSession) {
            return redirect()->route('pos');
        }

        $validated = $request->validate([
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        CashierSession::create([
            'user_id'         => Auth::id(),
            'opening_balance' => $validated['opening_balance'],
            'opened_at'       => now(),
            'status'          => 'open',
            'notes'           => $validated['notes'] ?? null,
        ]);

        return redirect()->route('pos')->with('success', 'Sesi kasir berhasil dibuka!');
    }

    public function closeForm()
    {
        $session = CashierSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->with(['orders' => function ($q) {
                $q->whereIn('status', ['paid', 'processing', 'ready', 'completed']);
            }])
            ->first();

        if (!$session) {
            return redirect()->route('cashier.session.open')->with('warning', 'Silakan buka sesi kasir terlebih dahulu.');
        }

        $cashSales = $session->orders->where('payment_method', 'cash')->sum('total');
        $qrisSales = $session->orders->where('payment_method', 'qris')->sum('total');
        $cardSales = $session->orders->where('payment_method', 'card')->sum('total');
        $totalSales = $session->orders->sum('total');
        $expectedCash = $session->opening_balance + $cashSales;

        return view('cashier.session.close', [
            'session'      => $session,
            'cashSales'    => $cashSales,
            'qrisSales'    => $qrisSales,
            'cardSales'    => $cardSales,
            'totalSales'   => $totalSales,
            'expectedCash' => $expectedCash,
            'orderCount'   => $session->orders->count(),
        ]);
    }

    public function updateSession(Request $request)
    {
        $session = CashierSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->firstOrFail();

        $validated = $request->validate([
            'closing_balance' => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $cashSales = $session->orders()->whereIn('status', ['paid', 'processing', 'ready', 'completed'])->where('payment_method', 'cash')->sum('total');
        $expectedCash = $session->opening_balance + $cashSales;
        $difference = $validated['closing_balance'] - $expectedCash;

        $session->update([
            'closing_balance'  => $validated['closing_balance'],
            'expected_balance' => $expectedCash,
            'difference'       => $difference,
            'closed_at'        => now(),
            'status'           => 'closed',
            'notes'            => $validated['notes'] ? ($session->notes . "\n" . $validated['notes']) : $session->notes,
        ]);

        return redirect()->route('cashier.session.open')->with('success', 'Sesi kasir berhasil ditutup.');
    }
}
