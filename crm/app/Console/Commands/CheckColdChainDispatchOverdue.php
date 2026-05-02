<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\WorkflowEngine;
use Illuminate\Console\Command;

class CheckColdChainDispatchOverdue extends Command
{
    protected $signature   = 'workflow:check-cold-chain-overdue';
    protected $description = 'Fire cold_chain.dispatch_overdue for any cold chain orders in processing > 4h.';

    public function __construct(private WorkflowEngine $engine)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $threshold = now()->subHours(4);

        $overdue = Order::where('status', Order::STATUS_PROCESSING)
            ->where('requires_cold_chain', true)
            ->where('created_at', '<=', $threshold)
            ->get();

        $this->info("Found {$overdue->count()} overdue cold chain order(s).");

        foreach ($overdue as $order) {
            $this->engine->fire('cold_chain.dispatch_overdue', $order);
            $this->line("  ↑ fired for order {$order->order_number}");
        }

        return self::SUCCESS;
    }
}
