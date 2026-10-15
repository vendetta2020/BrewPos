<?php

namespace App\Providers;

use App\Models\OrderItem;
use App\Observers\OrderItemObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Registers the OrderItemObserver to trigger inventory deduction
     * automatically on each completed transaction.
     *
     * Satisfies SRS: INV-05 — automatic stock deduction.
     * Follows SDD: section 4.2 — Observer registration pattern.
     */
    public function boot(): void
    {
        OrderItem::observe(OrderItemObserver::class);

        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('pos.pos-interface', \App\Http\Livewire\Pos\PosInterface::class);
            \Livewire\Livewire::component('pos.checkout-modal', \App\Http\Livewire\Pos\CheckoutModal::class);
            \Livewire\Livewire::component('kds.kds-board', \App\Http\Livewire\Kds\KdsBoard::class);
        }
    }
}
