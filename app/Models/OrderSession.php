<?php

namespace App\Models;

use App\Enums\SessionStatus;
use App\Models\Concerns\BelongsToHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OrderSession extends Model
{
    use BelongsToHotel, HasFactory;

    protected $fillable = [
        'hotel_id',
        'table_id',
        'session_token',
        'member_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => SessionStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderSession $session) {
            $session->session_token ??= (string) Str::uuid();
        });
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(SessionMember::class, 'session_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'session_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'session_id');
    }

    public function waiterCalls(): HasMany
    {
        return $this->hasMany(WaiterCall::class, 'session_id');
    }

    public function tableTransfers(): HasMany
    {
        return $this->hasMany(TableTransfer::class, 'session_id');
    }
}
