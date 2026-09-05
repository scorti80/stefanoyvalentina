<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class MediaUrl
{
    public function temporary(string $disk, ?string $key, array $options = []): ?string
    {
        if (! $key) {
            return null;
        }

        return Storage::disk($disk)->temporaryUrl(
            $key,
            now()->addMinutes(config('wedding.signed_url_lifetime')),
            $options,
        );
    }
}
