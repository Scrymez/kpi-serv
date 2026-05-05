<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KpiScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'points', 'reason',
        'reference_type', 'reference_id', 'academic_year_id',
    ];

    protected $casts = ['points' => 'float'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
