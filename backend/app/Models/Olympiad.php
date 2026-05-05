<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Olympiad extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'subject_id', 'level',
        'start_date', 'end_date', 'result_deadline',
        'source_url', 'source_type', 'created_by', 'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'result_deadline' => 'date',
        'is_active' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations()
    {
        return $this->hasMany(OlympiadRegistration::class);
    }

    public function getLevelLabelAttribute(): string
    {
        return match($this->level) {
            'school' => 'Школьный',
            'municipal' => 'Муниципальный',
            'regional' => 'Региональный',
            'federal' => 'Федеральный',
            'international' => 'Международный',
            default => '',
        };
    }

    public function getLevelCoefficientAttribute(): float
    {
        return match($this->level) {
            'school' => 1.0,
            'municipal' => 1.5,
            'regional' => 2.0,
            'federal' => 3.0,
            'international' => 5.0,
            default => 1.0,
        };
    }

    public function hasDateConflict(int $studentId): bool
    {
        return OlympiadRegistration::where('student_id', $studentId)
            ->whereHas('olympiad', function ($q) {
                $q->where('start_date', '<=', $this->end_date)
                  ->where('end_date', '>=', $this->start_date)
                  ->where('id', '!=', $this->id);
            })
            ->exists();
    }
}
