<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherVote extends Model
{
    use HasFactory;

    protected $fillable = ['nomination_id', 'teacher_id', 'voter_id'];

    public function nomination()
    {
        return $this->belongsTo(VoteNomination::class, 'nomination_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function voter()
    {
        return $this->belongsTo(User::class, 'voter_id');
    }
}

