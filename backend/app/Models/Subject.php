<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'coefficient'];

    protected $casts = ['coefficient' => 'float'];

    public function teachers()
    {
        return $this->hasMany(User::class)->where('role', 'teacher');
    }

    public function olympiads()
    {
        return $this->hasMany(Olympiad::class);
    }
}
