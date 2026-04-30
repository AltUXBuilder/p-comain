<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class IpWhitelistEntry extends Model
{
    protected $table = 'ip_whitelist';

    protected $fillable = [
        'ip_address',
        'label',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Bust the middleware cache whenever an entry changes
    protected static function boot(): void
    {
        parent::boot();

        $bust = fn () => Cache::forget('crm:ip_whitelist');

        static::created($bust);
        static::updated($bust);
        static::deleted($bust);
    }

    public function creator()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }
}
