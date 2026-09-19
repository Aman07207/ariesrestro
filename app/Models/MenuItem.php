<?php

namespace App\Models;

use App\Enums\TaxTrack;
use App\Models\Concerns\BelongsToHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MenuItem extends Model
{
    use BelongsToHotel, HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'price', 'is_available', 'is_popular', 'tax_track'])
            ->logOnlyDirty()
            ->useLogName('menu_item');
    }

    protected $fillable = [
        'hotel_id',
        'category_id',
        'name',
        'price',
        'veg_type',
        'is_available',
        'image',
        'avg_rating',
        'is_popular',
        'tax_track',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'avg_rating' => 'decimal:1',
            'is_popular' => 'boolean',
            'tax_track' => TaxTrack::class,
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
