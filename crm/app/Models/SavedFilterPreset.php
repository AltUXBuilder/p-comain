<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores per-staff saved search filter presets for the patient index.
 * Table created via inline migration below — add to a migration file.
 *
 * Migration SQL:
 *   CREATE TABLE saved_filter_presets (
 *     id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 *     staff_id BIGINT UNSIGNED NOT NULL,
 *     name VARCHAR(100) NOT NULL,
 *     filters JSON NOT NULL,
 *     created_at TIMESTAMP NULL,
 *     updated_at TIMESTAMP NULL,
 *     FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
 *   );
 */
class SavedFilterPreset extends Model
{
    protected $table = 'saved_filter_presets';

    protected $fillable = ['staff_id', 'name', 'filters'];

    protected $casts = ['filters' => 'array'];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
