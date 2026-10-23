<?php

use App\Http\Controllers\Api\PosQrisController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('pos/qris')->name('api.pos.qris.')->group(function () {
    Route::post('/generate', [PosQrisController::class, 'generate'])->name('generate');
    Route::get('/{reference}/status', [PosQrisController::class, 'status'])->name('status');
    Route::post('/callback', [PosQrisController::class, 'callback'])->name('callback');
    Route::post('/webhook', [PosQrisController::class, 'callback'])->name('webhook');
    Route::post('/simulate-pay', [PosQrisController::class, 'simulatePay'])->name('simulate');
});
