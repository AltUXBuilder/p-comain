<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = ['patient_id', 'prescription_id', 'status', 'carrier', 'tracking_number'];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
