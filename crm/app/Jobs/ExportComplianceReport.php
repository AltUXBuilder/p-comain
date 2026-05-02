<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\Document;
use App\Services\FinanceService;
use App\Services\PrescriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportComplianceReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes

    public function __construct(
        public string  $reportType,   // 'prescription_register' | 'audit_log' | 'rejection_register' | 'vat_report'
        public int     $requestedBy,  // staff id
        public array   $filters = []  // from/to/prescriber_id etc.
    ) {}

    public function handle(): void
    {
        $csv      = $this->generateCsv();
        $filename = $this->reportType . '-' . now()->format('Ymd-His') . '.csv';
        $path     = "exports/{$filename}";

        Storage::disk('private')->put($path, $csv);

        // Create a Document record so the staff member can download it
        $doc = Document::create([
            'user_id'              => null,
            'type'                 => Document::TYPE_SOP, // reusing for staff export — no patient link
            'name'                 => ucfirst(str_replace('_', ' ', $this->reportType)) . ' Export — ' . now()->format('d M Y H:i'),
            'path'                 => $path,
            'mime_type'            => 'text/csv',
            'size_bytes'           => strlen($csv),
            'uploaded_by_staff_id' => $this->requestedBy,
            'is_private'           => true,
        ]);

        // Notify the requesting staff member it's ready
        \App\Models\StaffNotification::create([
            'staff_id'    => $this->requestedBy,
            'type'        => 'export_ready',
            'message'     => "Your {$this->reportType} export is ready to download.",
            'entity_type' => 'document',
            'entity_id'   => $doc->id,
        ]);

        AuditLog::record(
            staffId:    $this->requestedBy,
            action:     "compliance_export_generated.{$this->reportType}",
            entityType: 'document',
            entityId:   $doc->id,
            metadata:   ['filters' => $this->filters],
            request:    request()
        );
    }

    private function generateCsv(): string
    {
        return match($this->reportType) {
            'prescription_register' => app(\App\Services\PrescriptionService::class)->exportRegister(
                from:         isset($this->filters['from']) ? new \DateTime($this->filters['from']) : null,
                to:           isset($this->filters['to'])   ? new \DateTime($this->filters['to'])   : null,
                prescriberId: $this->filters['prescriber_id'] ?? null,
                categoryId:   $this->filters['category_id']  ?? null,
            ),
            'vat_report' => app(\App\Services\FinanceService::class)->vatReportCsv(
                new \DateTime($this->filters['from'] ?? 'first day of last month'),
                new \DateTime($this->filters['to']   ?? 'last day of last month 23:59:59')
            ),
            'audit_log' => $this->generateAuditLogCsv(),
            default     => "Report type '{$this->reportType}' not implemented.",
        };
    }

    private function generateAuditLogCsv(): string
    {
        $query = AuditLog::with('staff')->orderByDesc('created_at');

        if ($from = ($this->filters['from'] ?? null)) $query->where('created_at', '>=', $from);
        if ($to   = ($this->filters['to']   ?? null)) $query->where('created_at', '<=', $to . ' 23:59:59');

        $rows = [['Timestamp', 'Staff', 'GPhC', 'Action', 'Entity', 'Entity ID', 'IP']];
        foreach ($query->cursor() as $log) {
            $rows[] = [
                $log->created_at->format('d/m/Y H:i:s'),
                $log->staff?->full_name ?? 'System',
                $log->gphc_number ?? '—',
                $log->action,
                $log->entity_type ?? '—',
                $log->entity_id   ?? '—',
                $log->ip_address  ?? '—',
            ];
        }

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $row)) . "\n";
        }
        return $csv;
    }
}
