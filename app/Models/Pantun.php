<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    public function scopeMasuk($query)
    {
        return $query->where('type', 'masuk');
    }

    /**
     * Scope untuk pantun pengingat pulang
     */
    public function scopePulang($query)
    {
        return $query->where('type', 'pulang');
    }
}
