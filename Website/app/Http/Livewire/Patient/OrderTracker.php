<?php

namespace App\Http\Livewire\Patient;

use App\Models\Order;
use Livewire\Component;

class OrderTracker extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order;
    }

    public function getStepsProperty(): array
    {
        $status = $this->order->status;

        $allSteps = [
            ['key' => 'payment_confirmed', 'label' => 'Payment confirmed',  'statuses' => ['payment_confirmed','processing','dispatched','delivered']],
            ['key' => 'processing',        'label' => 'Being prepared',      'statuses' => ['processing','dispatched','delivered']],
            ['key' => 'dispatched',        'label' => 'Dispatched',          'statuses' => ['dispatched','delivered']],
            ['key' => 'delivered',         'label' => 'Delivered',           'statuses' => ['delivered']],
        ];

        return array_map(function ($step) use ($status) {
            $step['done']    = in_array($status, $step['statuses']);
            $step['current'] = $status === $step['key'];
            return $step;
        }, $allSteps);
    }

    public function render()
    {
        return view('livewire.patient.order-tracker');
    }
}
