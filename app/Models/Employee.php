<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Employee
 *
 * @property int $id
 * @property string $name
 * @property string|null $panggilan
 * @property string $phone_number
 * @property bool $is_active
 * @method static Builder<static> query()
 * @method static Builder<static> where(string|\Closure|array $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static int count(string $columns = '*')
 * @method static static create(array $attributes = [])
 * @method static static findOrFail(mixed $id, array $columns = ['*'])
 * @method static static|null find(mixed $id, array $columns = ['*'])
 * @method static static|null first(array $columns = ['*'])
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
