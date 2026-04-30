<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GpSurgery extends Model
{
    protected $table = 'gp_surgeries';

    protected $fillable = ['name', 'address', 'phone', 'email', 'ods_code'];

    public function patients()
    {
        return $this->hasMany(Patient::class, 'gp_surgery_id');
    }
}
