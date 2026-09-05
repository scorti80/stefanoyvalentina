<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'last_synced_at' => 'datetime'];
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
}
