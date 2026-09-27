<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberSequence extends Model
{
    protected $fillable = ['scope', 'prefix', 'period', 'current_value'];

    protected function casts(): array
    {
        return [
            'period' => 'integer',
            'current_value' => 'integer',
        ];
    }
}
