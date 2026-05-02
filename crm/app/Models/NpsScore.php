<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NpsScore extends Model
{
    protected $table = 'nps_scores';

    protected $fillable = [
        'user_id',
        'order_id',
        'score',
        'comment',
        'category',
        'collected_at',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'score'        => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($nps) {
            $nps->category = self::categorise($nps->score);
        });
    }

    public static function categorise(int $score): string
    {
        if ($score <= 6) return 'detractor';
        if ($score <= 8) return 'passive';
        return 'promoter';
    }

    /**
     * Net Promoter Score = % promoters − % detractors
     */
    public static function calculateNps(\Illuminate\Database\Eloquent\Builder $query = null): ?float
    {
        $q      = $query ?? static::query();
        $total  = $q->count();
        if ($total === 0) return null;

        $cloned     = clone $q;
        $promoters  = (clone $q)->where('category', 'promoter')->count();
        $detractors = (clone $cloned)->where('category', 'detractor')->count();

        return round((($promoters - $detractors) / $total) * 100, 1);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
