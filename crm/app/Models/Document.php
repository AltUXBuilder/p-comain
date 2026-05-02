<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use SoftDeletes;

    protected $table = 'documents';

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'path',
        'mime_type',
        'size_bytes',
        'uploaded_by_staff_id',
        'is_private',
    ];

    protected $casts = [
        'is_private'  => 'boolean',
        'size_bytes'  => 'integer',
    ];

    // ── Type constants ────────────────────────────────────────────────────────

    const TYPE_PRESCRIPTION_PDF = 'prescription_pdf';
    const TYPE_PATIENT_UPLOAD   = 'patient_upload';
    const TYPE_IDENTITY_DOC     = 'identity_document';
    const TYPE_GP_LETTER        = 'gp_letter';
    const TYPE_SOP              = 'regulatory_sop';
    const TYPE_GPHC_DOC         = 'gphc_document';
    const TYPE_MHRA_DOC         = 'mhra_document';
    const TYPE_PIL              = 'pil';

    const TYPES = [
        self::TYPE_PRESCRIPTION_PDF => 'Prescription PDF',
        self::TYPE_PATIENT_UPLOAD   => 'Patient Upload',
        self::TYPE_IDENTITY_DOC     => 'Identity Document',
        self::TYPE_GP_LETTER        => 'GP Letter',
        self::TYPE_SOP              => 'Regulatory SOP',
        self::TYPE_GPHC_DOC         => 'GPhC Document',
        self::TYPE_MHRA_DOC         => 'MHRA Document',
        self::TYPE_PIL              => 'Patient Information Leaflet (PIL)',
    ];

    // Grouped for UI organisation
    const TYPES_GROUPED = [
        'Patient Documents' => [
            self::TYPE_PRESCRIPTION_PDF,
            self::TYPE_PATIENT_UPLOAD,
            self::TYPE_IDENTITY_DOC,
            self::TYPE_GP_LETTER,
        ],
        'Regulatory Vault'  => [
            self::TYPE_SOP,
            self::TYPE_GPHC_DOC,
            self::TYPE_MHRA_DOC,
        ],
        'Product Library'   => [
            self::TYPE_PIL,
        ],
    ];

    // Document types that must always be private storage
    const ALWAYS_PRIVATE = [
        self::TYPE_PRESCRIPTION_PDF,
        self::TYPE_PATIENT_UPLOAD,
        self::TYPE_IDENTITY_DOC,
        self::TYPE_GP_LETTER,
    ];

    // ── Boot ──────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        // Enforce private flag for patient-sensitive document types
        static::creating(function (Document $doc) {
            if (in_array($doc->type, self::ALWAYS_PRIVATE)) {
                $doc->is_private = true;
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function disk(): string
    {
        return $this->is_private ? 'private' : 'public';
    }

    public function exists(): bool
    {
        return Storage::disk($this->disk())->exists($this->path);
    }

    public function sizeFormatted(): string
    {
        if (! $this->size_bytes) return '—';
        $kb = $this->size_bytes / 1024;
        if ($kb < 1024) return round($kb, 1) . ' KB';
        return round($kb / 1024, 2) . ' MB';
    }

    public function isPdf(): bool
    {
        return in_array($this->mime_type, ['application/pdf', 'application/x-pdf']);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function typeColour(): string
    {
        return match($this->type) {
            self::TYPE_PRESCRIPTION_PDF => 'bg-plum-100 text-plum-700',
            self::TYPE_PATIENT_UPLOAD,
            self::TYPE_IDENTITY_DOC,
            self::TYPE_GP_LETTER        => 'bg-blue-100 text-blue-700',
            self::TYPE_SOP,
            self::TYPE_GPHC_DOC,
            self::TYPE_MHRA_DOC         => 'bg-amber-100 text-amber-700',
            self::TYPE_PIL              => 'bg-green-100 text-green-700',
            default                     => 'bg-plum-100 text-plum-600',
        };
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'user_id');
    }

    public function uploadedByStaff()
    {
        return $this->belongsTo(Staff::class, 'uploaded_by_staff_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePatientDocs($query)
    {
        return $query->whereIn('type', [
            self::TYPE_PRESCRIPTION_PDF,
            self::TYPE_PATIENT_UPLOAD,
            self::TYPE_IDENTITY_DOC,
            self::TYPE_GP_LETTER,
        ]);
    }

    public function scopeRegulatoryVault($query)
    {
        return $query->whereIn('type', [
            self::TYPE_SOP,
            self::TYPE_GPHC_DOC,
            self::TYPE_MHRA_DOC,
        ]);
    }

    public function scopePils($query)
    {
        return $query->where('type', self::TYPE_PIL);
    }
}
