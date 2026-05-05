<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VoteNomination extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function votes()
    {
        return $this->hasMany(TeacherVote::class, 'nomination_id');
    }
}

