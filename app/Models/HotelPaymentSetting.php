<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class HotelPaymentSetting extends Model
{
    use BelongsToHotel, HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        // Deliberately excludes razorpay_key_id/razorpay_key_secret — logging their
        // values (even as "changed") would undermine the whole point of encrypting them.
        return LogOptions::defaults()
            ->logOnly(['razorpay_linked_account_id', 'commission_percent'])
            ->logOnlyDirty()
            ->useLogName('payment_setting');
    }

    protected $fillable = [
        'hotel_id',
        'razorpay_linked_account_id',
        'razorpay_key_id',
        'razorpay_key_secret',
        'commission_percent',
    ];

    protected function casts(): array
    {
        return [
            // Never stored in plain text, even inside our own database.
            'razorpay_key_id' => 'encrypted',
            'razorpay_key_secret' => 'encrypted',
            'commission_percent' => 'decimal:2',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
