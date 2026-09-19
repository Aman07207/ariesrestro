<?php

namespace App\Models;

use App\Enums\HotelStatus;
use App\Enums\WeatherThemeMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Hotel extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'status', 'subscription_plan', 'gst_rate', 'vat_rate', 'is_luxury_hotel', 'default_service_charge_percent', 'gstin', 'google_review_link', 'weather_theme_mode'])
            ->logOnlyDirty()
            ->useLogName('hotel');
    }

    protected $fillable = [
        'name',
        'slug',
        'gstin',
        'logo',
        'google_review_link',
        'owner_name',
        'owner_email',
        'owner_phone',
        'address',
        'subscription_plan',
        'status',
        'latitude',
        'longitude',
        'geofence_radius_meters',
        'geofence_enabled',
        'gst_rate',
        'vat_rate',
        'is_luxury_hotel',
        'default_service_charge_percent',
        'weather_theme_mode',
    ];

    protected function casts(): array
    {
        return [
            'status' => HotelStatus::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'geofence_enabled' => 'boolean',
            'gst_rate' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'is_luxury_hotel' => 'boolean',
            'default_service_charge_percent' => 'decimal:2',
            'weather_theme_mode' => WeatherThemeMode::class,
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function menuCategories(): HasMany
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orderSessions(): HasMany
    {
        return $this->hasMany(OrderSession::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function paymentSettings(): HasOne
    {
        return $this->hasOne(HotelPaymentSetting::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }
}
