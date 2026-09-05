<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Playlist extends Model
{
    protected $guarded = [];

    protected $hidden = ['share_token', 'share_token_hash', 'pin_hash'];

    protected function casts(): array
    {
        return [
            'share_token' => 'encrypted',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'allow_sd_download' => 'boolean',
            'allow_hd_download' => 'boolean',
            'allow_zip_download' => 'boolean',
        ];
    }

    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(Photo::class)->withPivot('position')->orderByPivot('position');
    }

    public function getShareUrlAttribute(): string
    {
        return route('gallery.show', ['token' => $this->share_token]);
    }

    public function isAvailable(): bool
    {
        return $this->is_active && (! $this->expires_at || $this->expires_at->isFuture());
    }
}
