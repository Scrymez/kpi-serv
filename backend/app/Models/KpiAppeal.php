<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KpiAppeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'kpi_score_id', 'reason',
        'status', 'resolved_by', 'resolved_at', 'resolution_note',
    ];

    protected $casts = ['resolved_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kpiScore()
    {
        return $this->belongsTo(KpiScore::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
