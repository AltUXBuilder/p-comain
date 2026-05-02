<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Records every time a GP surgery has been notified about a patient.
 *
 * Migration:
 *   CREATE TABLE gp_notifications (
 *     id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 *     patient_id BIGINT UNSIGNED NOT NULL,
 *     staff_id BIGINT UNSIGNED NOT NULL,
 *     prescription_id BIGINT UNSIGNED NULL,
 *     method ENUM('letter','email','phone','fax','other') NOT NULL,
 *     notes TEXT NOT NULL,
 *     notified_at TIMESTAMP NOT NULL,
 *     created_at TIMESTAMP NULL,
 *     updated_at TIMESTAMP NULL,
 *     FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
 *     FOREIGN KEY (staff_id) REFERENCES staff(id),
 *     FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE SET NULL
 *   );
 */
class GpNotification extends Model
{
    protected $table = 'gp_notifications';

    protected $fillable = [
        'patient_id',
        'staff_id',
        'prescription_id',
        'method',
        'notes',
        'notified_at',
    ];

    protected $casts = ['notified_at' => 'datetime'];

    const METHODS = [
        'letter' => 'Letter',
        'email'  => 'Email',
        'phone'  => 'Phone call',
        'fax'    => 'Fax',
        'other'  => 'Other',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
