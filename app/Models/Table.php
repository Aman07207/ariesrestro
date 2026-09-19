<?php

namespace App\Models;

use App\Enums\TableStatus;
use App\Models\Concerns\BelongsToHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Table extends Model
{
    use BelongsToHotel, HasFactory;

    protected $table = 'tables';

    protected $fillable = [
        'hotel_id',
        'table_number',
        'table_uuid',
        'seating_capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => TableStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Table $table) {
            $table->table_uuid ??= (string) Str::uuid();
        });
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function orderSessions(): HasMany
    {
        return $this->hasMany(OrderSession::class);
    }
}
