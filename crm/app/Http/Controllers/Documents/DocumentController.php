<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Patient;
use App\Models\Product;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function __construct(private DocumentService $service) {}

    // ── Patient document archive ──────────────────────────────────────────────

    /**
     * All documents for a specific patient.
     * GET /patients/{patient}/documents
     */
    public function patientIndex(Patient $patient)
    {
        $docs = Document::where('user_id', $patient->id)
            ->with('uploadedByStaff')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('type');

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'patient_documents_viewed',
            entityType: 'patient',
            entityId:   $patient->id,
            request:    request()
        );

        return view('documents.patient', compact('patient', 'docs'));
    }

    /**
     * Upload a document for a patient.
     * POST /patients/{patient}/documents
     */
    public function patientUpload(Request $request, Patient $patient)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:20480', // 20 MB
                'mimes:pdf,jpg,jpeg,png,doc,docx',
            ],
            'type' => [
                'required',
                'in:' . implode(',', [
                    Document::TYPE_PATIENT_UPLOAD,
                    Document::TYPE_IDENTITY_DOC,
                    Document::TYPE_GP_LETTER,
                ]),
            ],
            'name' => ['nullable', 'string', 'max:200'],
        ]);

        $doc = $this->service->upload(
            file:       $request->file('file'),
            type:       $request->type,
            uploader:   Auth::guard('staff')->user(),
            patient:    $patient,
            customName: $request->name,
        );

        return back()->with('success', "Document '{$doc->name}' uploaded.");
    }

    // ── Prescription PDF archive ──────────────────────────────────────────────

    /**
     * All prescription PDFs across all patients — searchable archive.
     * GET /documents/prescriptions
     */
    public function prescriptionArchive(Request $request)
    {
        $query = Document::where('type', Document::TYPE_PRESCRIPTION_PDF)
            ->with(['patient', 'uploadedByStaff'])
            ->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn ($q2) =>
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name',  'like', "%{$search}%")
                  );
            });
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $docs = $query->paginate(30)->withQueryString();

        return view('documents.prescription-archive', compact('docs'));
    }

    // ── Regulatory vault ──────────────────────────────────────────────────────

    /**
     * SOPs, GPhC documents, MHRA documents.
     * GET /documents/vault
     */
    public function vault(Request $request)
    {
        $query = Document::regulatoryVault()
            ->with('uploadedByStaff')
            ->orderByDesc('created_at');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $docs = $query->paginate(25)->withQueryString();

        $types = [
            Document::TYPE_SOP      => Document::TYPES[Document::TYPE_SOP],
            Document::TYPE_GPHC_DOC => Document::TYPES[Document::TYPE_GPHC_DOC],
            Document::TYPE_MHRA_DOC => Document::TYPES[Document::TYPE_MHRA_DOC],
        ];

        return view('documents.vault', compact('docs', 'types'));
    }

    /**
     * Upload to regulatory vault (Super Admin / Superintendent only).
     * POST /documents/vault
     */
    public function vaultUpload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200', 'mimes:pdf,doc,docx,xls,xlsx'],
            'type' => ['required', 'in:' . implode(',', [
                Document::TYPE_SOP,
                Document::TYPE_GPHC_DOC,
                Document::TYPE_MHRA_DOC,
            ])],
            'name' => ['required', 'string', 'max:200'],
        ]);

        $doc = $this->service->upload(
            file:      $request->file('file'),
            type:      $request->type,
            uploader:  Auth::guard('staff')->user(),
            customName: $request->name,
        );

        return back()->with('success', "Document '{$doc->name}' added to vault.");
    }

    // ── PIL library ───────────────────────────────────────────────────────────

    /**
     * All Patient Information Leaflets, optionally linked to products.
     * GET /documents/pils
     */
    public function pilLibrary(Request $request)
    {
        $pils = Document::pils()
            ->with('uploadedByStaff')
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        // Products for the attach-to-product form
        $products = Product::where('active', true)->orderBy('name')->get();

        return view('documents.pils', compact('pils', 'products'));
    }

    /**
     * Upload a PIL.
     * POST /documents/pils
     */
    public function pilUpload(Request $request)
    {
        $request->validate([
            'file'       => ['required', 'file', 'max:20480', 'mimes:pdf'],
            'name'       => ['required', 'string', 'max:200'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        $doc = $this->service->upload(
            file:      $request->file('file'),
            type:      Document::TYPE_PIL,
            uploader:  Auth::guard('staff')->user(),
            customName: $request->name,
        );

        // Optionally attach to a product
        if ($request->product_id) {
            $product = Product::findOrFail($request->product_id);
            $this->service->attachPilToProduct($doc, $product, Auth::guard('staff')->user());
        }

        return back()->with('success', "PIL '{$doc->name}' uploaded" . ($request->product_id ? ' and attached to product.' : '.'));
    }

    /**
     * Attach an existing PIL to a product.
     * POST /documents/pils/{document}/attach
     */
    public function pilAttach(Request $request, Document $document)
    {
        $request->validate(['product_id' => ['required', 'exists:products,id']]);

        $product = Product::findOrFail($request->product_id);
        $this->service->attachPilToProduct($document, $product, Auth::guard('staff')->user());

        return back()->with('success', "PIL attached to {$product->name}.");
    }

    // ── Stream & download ─────────────────────────────────────────────────────

    /**
     * Stream a document inline in the browser.
     * GET /documents/{document}/view
     */
    public function view(Document $document)
    {
        // Access control: patient docs require clinical role
        if (in_array($document->type, Document::ALWAYS_PRIVATE)) {
            abort_unless(
                Auth::guard('staff')->user()->hasAnyRole([
                    'super_admin',
                    'superintendent_pharmacist',
                    'prescriber',
                    'customer_support',
                ]),
                403
            );
        }

        return $this->service->stream($document, Auth::guard('staff')->user());
    }

    /**
     * Force-download a document.
     * GET /documents/{document}/download
     */
    public function download(Document $document)
    {
        if (in_array($document->type, Document::ALWAYS_PRIVATE)) {
            abort_unless(
                Auth::guard('staff')->user()->hasAnyRole([
                    'super_admin',
                    'superintendent_pharmacist',
                    'prescriber',
                    'customer_support',
                ]),
                403
            );
        }

        return $this->service->download($document, Auth::guard('staff')->user());
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    /**
     * Delete a document (not prescriptions).
     * DELETE /documents/{document}
     */
    public function destroy(Document $document)
    {
        try {
            $this->service->delete($document, Auth::guard('staff')->user());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', "'{$document->name}' deleted.");
    }
}
