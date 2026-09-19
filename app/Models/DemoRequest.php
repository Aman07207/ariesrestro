<?php

namespace App\Models;

use App\Enums\DemoRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemoRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hotel_name',
        'phone',
        'email',
        'message',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => DemoRequestStatus::class,
        ];
    }
}
