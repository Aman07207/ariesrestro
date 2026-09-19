<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'old_table_id',
        'new_table_id',
        'transferred_by',
        'reason',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(OrderSession::class, 'session_id');
    }

    public function oldTable(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'old_table_id');
    }

    public function newTable(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'new_table_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
