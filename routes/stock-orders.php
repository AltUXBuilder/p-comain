<?php

/**
 * Phase 10 & 11 routes — merge into routes/web.php.
 *
 * Replaces stubs:
 *   Route::get('/stock',  fn () => view('coming-soon', ...))->name('stock.index');
 *   Route::get('/orders', fn () => view('coming-soon', ...))->name('orders.index');
 */

use App\Http\Controllers\Stock\StockController;
use App\Http\Controllers\Orders\OrderController;
use Illuminate\Support\Facades\Route;

// ── Phase 10: Inventory & Stock ───────────────────────────────────────────────

Route::prefix('stock')
    ->name('stock.')
    ->middleware('role:super_admin,superintendent_pharmacist,dispenser')
    ->group(function () {

        Route::get('/',                           [StockController::class, 'index'])->name('index');
        Route::get('/near-expiry',                [StockController::class, 'nearExpiry'])->name('near-expiry');
        Route::get('/suppliers',                  [StockController::class, 'suppliers'])->name('suppliers');
        Route::post('/suppliers',                 [StockController::class, 'storeSupplier'])->name('suppliers.store');
        Route::get('/purchase-orders',            [StockController::class, 'purchaseOrders'])->name('purchase-orders');
        Route::post('/purchase-orders',           [StockController::class, 'storePurchaseOrder'])->name('purchase-orders.store');
        Route::patch('/purchase-orders/{po}/status', [StockController::class, 'updatePurchaseOrderStatus'])->name('purchase-orders.status');

        Route::get('/{product}',                  [StockController::class, 'show'])->name('show');
        Route::post('/{product}/receive',         [StockController::class, 'receiveBatch'])->name('receive');
        Route::post('/{product}/threshold',       [StockController::class, 'updateThreshold'])->name('threshold');
        Route::post('/{product}/reconcile',       [StockController::class, 'reconcile'])->name('reconcile');
        Route::post('/batch/{batch}/write-off',   [StockController::class, 'writeOff'])->name('write-off');
    });

// ── Phase 11: Orders & Fulfilment ─────────────────────────────────────────────

Route::prefix('orders')
    ->name('orders.')
    ->middleware('role:super_admin,superintendent_pharmacist,dispenser,customer_support')
    ->group(function () {

        Route::get('/',                              [OrderController::class, 'index'])->name('index');
        Route::get('/manifest-export',               [OrderController::class, 'manifestExport'])->name('manifest-export')
            ->middleware('role:super_admin,superintendent_pharmacist,dispenser');

        Route::post('/batch-dispatch',               [OrderController::class, 'batchDispatch'])->name('batch-dispatch')
            ->middleware('role:super_admin,superintendent_pharmacist,dispenser');

        Route::get('/{order}',                       [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/dispatch',             [OrderController::class, 'dispatch'])->name('dispatch')
            ->middleware('role:super_admin,superintendent_pharmacist,dispenser');
        Route::post('/{order}/delivered',            [OrderController::class, 'markDelivered'])->name('delivered');
        Route::post('/{order}/failed-delivery',      [OrderController::class, 'markFailedDelivery'])->name('failed-delivery');
        Route::post('/{order}/returned',             [OrderController::class, 'markReturned'])->name('returned');
        Route::post('/{order}/reship',               [OrderController::class, 'reship'])->name('reship')
            ->middleware('role:super_admin,superintendent_pharmacist,dispenser');
        Route::patch('/{order}/notes',               [OrderController::class, 'updateNotes'])->name('notes');
    });
