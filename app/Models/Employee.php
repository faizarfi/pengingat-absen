<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Employee
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Employee extends Model
{
    protected $fillable = [
        'name',
        'panggilan',
        'phone_number',
        'is_active',
    ];
}
