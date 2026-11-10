<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Employee
 *
 * @property int $id
 * @property string $name
 * @property string|null $panggilan
 * @property string $phone_number
 * @property bool $is_active
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
