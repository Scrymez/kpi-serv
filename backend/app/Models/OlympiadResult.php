<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OlympiadResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id', 'place', 'is_winner',
        'document_path', 'document_original_name',
        'uploaded_by', 'uploaded_at',
        'status', 'verified_by', 'verified_at', 'reject_reason',
    ];

    protected $casts = [
        'is_winner' => 'boolean',
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function registration()
    {
        return $this->belongsTo(OlympiadRegistration::class, 'registration_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
