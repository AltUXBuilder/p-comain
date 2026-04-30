<?php

namespace App\Console\Commands;

use App\Models\Prescription;
use App\Services\PrescriptionService;
use Illuminate\Console\Command;

class GenerateRepeatPrescriptions extends Command
{
    protected $signature   = 'prescriptions:generate-repeats';
    protected $description = 'Auto-generate pending review prescriptions for dispensed repeats that are due.';

    public function __construct(private PrescriptionService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $due = Prescription::where('is_repeat', true)
            ->where('status', Prescription::STATUS_DISPENSED)
            ->whereNotNull('next_repeat_due')
            ->whereDate('next_repeat_due', '<=', today())
            // Ensure we haven't already generated a child for this cycle
            ->whereDoesntHave('repeatChildren', fn ($q) =>
                $q->whereIn('status', [
                    Prescription::STATUS_PENDING_REVIEW,
                    Prescription::STATUS_APPROVED,
                    Prescription::STATUS_SENT_TO_DISPENSE,
                ])
            )
            ->get();

        $this->info("Found {$due->count()} repeat(s) due.");

        $generated = 0;
        foreach ($due as $parent) {
            try {
                $child = $this->service->generateRepeat($parent);
                $this->line("  ✓ Generated {$child->prescription_number} from {$parent->prescription_number}");
                $generated++;
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed for {$parent->prescription_number}: {$e->getMessage()}");
            }
        }

        $this->info("Generated {$generated} repeat prescription(s).");
        return self::SUCCESS;
    }
}
