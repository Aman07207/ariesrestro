<?php

namespace App\Models;

use App\Enums\BillStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'hotel_id',
        'food_base_amount',
        'alcohol_base_amount',
        'service_charge_percent',
        'service_charge_amount',
        'service_charge_consent',
        'gst_rate',
        'cgst_amount',
        'sgst_amount',
        'vat_rate',
        'vat_amount',
        'grand_total',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'food_base_amount' => 'decimal:2',
            'alcohol_base_amount' => 'decimal:2',
            'service_charge_percent' => 'decimal:2',
            'service_charge_amount' => 'decimal:2',
            'service_charge_consent' => 'boolean',
            'gst_rate' => 'decimal:2',
            'cgst_amount' => 'decimal:2',
            'sgst_amount' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'status' => BillStatus::class,
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(OrderSession::class, 'session_id');
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
