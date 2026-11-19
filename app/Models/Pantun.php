<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Pantun
 *
 * @property int $id
 * @property string $type
 * @property string $text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder<static> query()
 * @method static Builder<static> masuk()
 * @method static Builder<static> pulang()
 * @method static static create(array $attributes = [])
 * @method static static|null find(mixed $id, array $columns = ['*'])
 */
class Pantun extends Model
{
    protected $table = 'pantuns';

    protected $fillable = [
        'type',
        'text',
    ];

    /**
     * Scope untuk pantun pengingat masuk
     */
    public function scopeMasuk(Builder $query): Builder
    {
        return $query->where('type', 'masuk');
    }

    /**
     * Scope untuk pantun pengingat pulang
     */
    public function scopePulang(Builder $query): Builder
    {
        return $query->where('type', 'pulang');
    }
}
