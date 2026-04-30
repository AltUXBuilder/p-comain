<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'type', 'name', 'path', 'mime_type',
        'size_bytes', 'uploaded_by_staff_id', 'is_private',
    ];

    protected function casts(): array
    {
        return ['is_private' => 'boolean'];
    }

    public function getSignedUrl(int $minutesValid = 30): string
    {
        return Storage::disk('private')->temporaryUrl($this->path, now()->addMinutes($minutesValid));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uploadedByStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'uploaded_by_staff_id');
    }
}
