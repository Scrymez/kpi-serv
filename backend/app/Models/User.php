<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'last_name', 'first_name', 'middle_name',
        'login', 'password', 'role',
        'class_id', 'subject_id', 'age',
        'is_class_teacher', 'class_teacher_class_id', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_class_teacher' => 'boolean',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->last_name} {$this->first_name} {$this->middle_name}");
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classTeacherClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_teacher_class_id');
    }

    public function kpiScores()
    {
        return $this->hasMany(KpiScore::class);
    }

    public function registrations()
    {
        return $this->hasMany(OlympiadRegistration::class, 'student_id');
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isDirector(): bool { return $this->role === 'director'; }
    public function isDeputy(): bool { return in_array($this->role, ['deputy_events', 'deputy_edu', 'deputy_science']); }
    public function isTeacher(): bool { return $this->role === 'teacher'; }
    public function isStudent(): bool { return $this->role === 'student'; }

    public function canVerify(): bool
    {
        return in_array($this->role, ['director', 'deputy_events', 'deputy_edu', 'deputy_science', 'admin']);
    }

    public function canManageUsers(): bool
    {
        return $this->role === 'admin';
    }
}
