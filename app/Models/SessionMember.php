<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'member_no',
        'device_token',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(OrderSession::class, 'session_id');
    }
}
