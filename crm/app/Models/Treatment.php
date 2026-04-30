<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    protected $table = 'treatments';

    public function category()
    {
        return $this->belongsTo(TreatmentCategory::class, 'treatment_category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
