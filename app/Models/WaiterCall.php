<?php

namespace App\Models;

use App\Enums\WaiterCallStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaiterCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'table_id',
        'status',
        'note',
        'attended_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => WaiterCallStatus::class,
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(OrderSession::class, 'session_id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function attendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attended_by');
    }
}
