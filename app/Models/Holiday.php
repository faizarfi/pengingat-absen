<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $table = 'holidays';

    protected $fillable = [
        'date',
        'name',
        'is_national_holiday',
        'description',
    ];

    protected $casts = [
        'date'                => 'date',
        'is_national_holiday' => 'boolean',
    ];

    public static function isHolidayToday(): bool
    {
        return static::where('date', now()->toDateString())->exists();
    }
}
