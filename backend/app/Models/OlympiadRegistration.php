<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OlympiadRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'olympiad_id', 'student_id', 'registered_by',
        'teacher_id', 'teacher2_id', 'status',
    ];

    public function olympiad()
    {
        return $this->belongsTo(Olympiad::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function teacher2()
    {
        return $this->belongsTo(User::class, 'teacher2_id');
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function result()
    {
        return $this->hasOne(OlympiadResult::class, 'registration_id');
    }
}
