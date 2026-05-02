<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\Staff;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    // ── Upload ────────────────────────────────────────────────────────────────

    /**
     * Store an uploaded file as a Document record.
     *
     * @param  UploadedFile  $file
     * @param  string        $type        Document::TYPE_* constant
     * @param  Staff         $uploader
     * @param  Patient|null  $patient     Required for patient document types
     * @param  string|null   $customName  Override file name shown in UI
     */
    public function upload(
        UploadedFile $file,
        string       $type,
        Staff        $uploader,
        ?Patient     $patient     = null,
        ?string      $customName  = null
    ): Document {
        $isPrivate = in_array($type, Document::ALWAYS_PRIVATE) || $patient !== null;
        $disk      = $isPrivate ? 'private' : 'public';

        // Build storage path
        $folder = match($type) {
            Document::TYPE_PRESCRIPTION_PDF => "documents/prescriptions",
            Document::TYPE_PATIENT_UPLOAD,
            Document::TYPE_IDENTITY_DOC,
            Document::TYPE_GP_LETTER        => "documents/patients/{$patient?->id}",
            Document::TYPE_SOP,
            Document::TYPE_GPHC_DOC,
            Document::TYPE_MHRA_DOC         => "documents/regulatory",
            Document::TYPE_PIL              => "documents/pils",
            default                         => "documents/misc",
        };

        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '_' . now()->format('Ymd_His')
            . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs($folder, $filename, $disk);

        $doc = Document::create([
            'user_id'              => $patient?->id,
            'type'                 => $type,
            'name'                 => $customName ?? $file->getClientOriginalName(),
            'path'                 => $path,
            'mime_type'            => $file->getMimeType(),
            'size_bytes'           => $file->getSize(),
            'uploaded_by_staff_id' => $uploader->id,
            'is_private'           => $isPrivate,
        ]);

        AuditLog::record(
            staffId:    $uploader->id,
            action:     'document_uploaded',
            entityType: 'document',
            entityId:   $doc->id,
            metadata:   ['type' => $type, 'name' => $doc->name, 'patient_id' => $patient?->id],
            request:    request()
        );

        return $doc;
    }

    // ── Archive prescription PDF ──────────────────────────────────────────────

    /**
     * Register an already-generated prescription PDF as a Document record.
     * Called from PrescriptionService::sign() after PDF generation.
     */
    public function archivePrescriptionPdf(Prescription $prescription, Staff $prescriber): Document
    {
        // Avoid duplicate archive entries
        $existing = Document::where('type', Document::TYPE_PRESCRIPTION_PDF)
            ->where('path', $prescription->pdf_path)
            ->first();

        if ($existing) return $existing;

        $stat = Storage::disk('private')->exists($prescription->pdf_path)
            ? Storage::disk('private')->size($prescription->pdf_path)
            : null;

        return Document::create([
            'user_id'              => $prescription->patient_id,
            'type'                 => Document::TYPE_PRESCRIPTION_PDF,
            'name'                 => "{$prescription->prescription_number}.pdf",
            'path'                 => $prescription->pdf_path,
            'mime_type'            => 'application/pdf',
            'size_bytes'           => $stat,
            'uploaded_by_staff_id' => $prescriber->id,
            'is_private'           => true,
        ]);
    }

    // ── Stream / download ─────────────────────────────────────────────────────

    public function stream(Document $doc, Staff $viewer): \Symfony\Component\HttpFoundation\Response
    {
        abort_unless($doc->exists(), 404, 'Document file not found.');

        AuditLog::record(
            staffId:    $viewer->id,
            action:     'document_viewed',
            entityType: 'document',
            entityId:   $doc->id,
            request:    request()
        );

        return response()->file(
            Storage::disk($doc->disk())->path($doc->path),
            [
                'Content-Type'        => $doc->mime_type ?? 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . $doc->name . '"',
            ]
        );
    }

    public function download(Document $doc, Staff $downloader): \Symfony\Component\HttpFoundation\Response
    {
        abort_unless($doc->exists(), 404, 'Document file not found.');

        AuditLog::record(
            staffId:    $downloader->id,
            action:     'document_downloaded',
            entityType: 'document',
            entityId:   $doc->id,
            request:    request()
        );

        return Storage::disk($doc->disk())->download($doc->path, $doc->name);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    /**
     * Soft-delete the document record and remove the physical file.
     * Prescription PDFs cannot be deleted (regulatory requirement).
     */
    public function delete(Document $doc, Staff $actor): void
    {
        if ($doc->type === Document::TYPE_PRESCRIPTION_PDF) {
            throw new \RuntimeException('Prescription PDFs cannot be deleted — they are retained for regulatory compliance.');
        }

        // Remove physical file
        if ($doc->exists()) {
            Storage::disk($doc->disk())->delete($doc->path);
        }

        $doc->delete(); // soft delete

        AuditLog::record(
            staffId:    $actor->id,
            action:     'document_deleted',
            entityType: 'document',
            entityId:   $doc->id,
            metadata:   ['name' => $doc->name, 'type' => $doc->type],
            request:    request()
        );
    }

    // ── Right to erasure: redact patient documents ────────────────────────────

    /**
     * Called from DsarService::erasePatient().
     * Physically deletes patient document files and soft-deletes records.
     * Prescription PDFs are explicitly retained and skipped.
     */
    public function redactPatientDocuments(int $patientId, Staff $actor): int
    {
        $docs = Document::where('user_id', $patientId)
            ->where('type', '!=', Document::TYPE_PRESCRIPTION_PDF)
            ->get();

        $count = 0;
        foreach ($docs as $doc) {
            if ($doc->exists()) {
                Storage::disk($doc->disk())->delete($doc->path);
            }
            $doc->delete();
            $count++;
        }

        AuditLog::record(
            staffId:    $actor->id,
            action:     'patient_documents_redacted',
            entityType: 'user',
            entityId:   $patientId,
            metadata:   ['documents_removed' => $count, 'prescription_pdfs_retained' => true],
            request:    request()
        );

        return $count;
    }

    // ── PIL management ────────────────────────────────────────────────────────

    /**
     * Attach a PIL document to a product (updates products.pil_path).
     */
    public function attachPilToProduct(Document $pil, Product $product, Staff $actor): void
    {
        if ($pil->type !== Document::TYPE_PIL) {
            throw new \RuntimeException('Document is not a PIL.');
        }

        $product->update(['pil_path' => $pil->path]);

        AuditLog::record(
            staffId:    $actor->id,
            action:     'pil_attached_to_product',
            entityType: 'product',
            entityId:   $product->id,
            metadata:   ['document_id' => $pil->id, 'product_id' => $product->id],
            request:    request()
        );
    }

    // ── Signed URL for private file access ───────────────────────────────────

    /**
     * Generate a temporary signed URL for a private document.
     * Used for patient-facing download links and iframe previews.
     */
    public function temporaryUrl(Document $doc, int $minutes = 10): string
    {
        if (! $doc->is_private) {
            return Storage::disk('public')->url($doc->path);
        }

        return Storage::disk('private')->temporaryUrl(
            $doc->path,
            now()->addMinutes($minutes)
        );
    }
}
