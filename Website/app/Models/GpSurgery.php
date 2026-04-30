<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GpSurgery extends Model
{
    protected $fillable = [
        'name', 'ods_code', 'address_line_1', 'address_line_2',
        'city', 'postcode', 'phone', 'email', 'fax', 'active',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function patients(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('name');
    }
}
