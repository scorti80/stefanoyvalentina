<?php

namespace App\Services;

use App\Models\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaSyncService
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'heic'];

    public function sync(Collection $collection): array
    {
        $disk = Storage::disk($collection->disk);
        $webFiles = $this->imageFiles($disk->allFiles($this->prefix($collection->web_prefix)));
        $hdFiles = $this->imageFiles($disk->allFiles($this->prefix($collection->hd_prefix)));
        $webMap = $this->keyByRelativeName($webFiles, $collection->web_prefix);
        $hdMap = $this->keyByRelativeName($hdFiles, $collection->hd_prefix);
        $names = collect(array_keys($webMap))->merge(array_keys($hdMap))->unique()->sort()->values();
        $seen = [];

        DB::transaction(function () use ($collection, $disk, $names, $webMap, $hdMap, &$seen): void {
            foreach ($names as $index => $normalizedName) {
                $webKey = $webMap[$normalizedName] ?? null;
                $hdKey = $hdMap[$normalizedName] ?? null;
                $displayKey = $webKey ?: $hdKey;
                $filename = $this->relativeName($displayKey, $webKey ? $collection->web_prefix : $collection->hd_prefix);

                $photo = $collection->photos()->updateOrCreate(
                    ['filename' => $filename],
                    [
                        'web_key' => $webKey,
                        'hd_key' => $hdKey,
                        'mime_type' => $displayKey ? $this->safe(fn () => $disk->mimeType($displayKey)) : null,
                        'web_bytes' => $webKey ? $this->safe(fn () => $disk->size($webKey)) : null,
                        'hd_bytes' => $hdKey ? $this->safe(fn () => $disk->size($hdKey)) : null,
                        'sort_order' => $index,
                        'is_active' => true,
                    ],
                );

                $seen[] = $photo->id;
            }

            $collection->photos()->when($seen, fn ($query) => $query->whereNotIn('id', $seen))->update(['is_active' => false]);
            $collection->update(['last_synced_at' => now()]);
        });

        return [
            'total' => count($seen),
            'web' => count($webFiles),
            'hd' => count($hdFiles),
            'missing_web' => count(array_diff(array_keys($hdMap), array_keys($webMap))),
            'missing_hd' => count(array_diff(array_keys($webMap), array_keys($hdMap))),
        ];
    }

    private function prefix(string $prefix): string
    {
        return trim($prefix, '/');
    }

    private function imageFiles(array $files): array
    {
        return array_values(array_filter($files, fn (string $file) => in_array(Str::lower(pathinfo($file, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true)));
    }

    private function keyByRelativeName(array $files, string $prefix): array
    {
        $map = [];

        foreach ($files as $file) {
            $map[Str::lower($this->relativeName($file, $prefix))] = $file;
        }

        return $map;
    }

    private function relativeName(string $key, string $prefix): string
    {
        return ltrim(Str::after($key, trim($prefix, '/')), '/');
    }

    private function safe(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (\Throwable) {
            return null;
        }
    }
}
