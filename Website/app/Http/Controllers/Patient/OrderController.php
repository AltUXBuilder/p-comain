<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $orders = auth()->user()
            ->orders()
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        return view('patient.orders', compact('orders'));
    }

    public function show(Order $order): \Illuminate\View\View
    {
        if ($order->user_id !== auth()->id()) abort(403);
        $order->load(['items.product', 'prescription', 'invoice']);
        return view('patient.order-detail', compact('order'));
    }
}
