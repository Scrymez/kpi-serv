<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'school_classes';

    protected $fillable = ['name', 'grade_number', 'education_level'];

    public function students()
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'student');
    }

    public function classTeacher()
    {
        return $this->hasOne(User::class, 'class_teacher_class_id')->where('is_class_teacher', true);
    }

    public function getEducationLevelLabelAttribute(): string
    {
        return match($this->education_level) {
            'primary' => 'Начальное общее (1-4)',
            'basic' => 'Основное общее (5-9)',
            'secondary' => 'Среднее общее (10-11)',
            default => '',
        };
    }
}
