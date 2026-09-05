<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Services\MediaSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_pairs_web_and_hd_files_without_duplicating_photos(): void
    {
        Storage::fake('s3');
        Storage::disk('s3')->put('wedding/one/web/IMG_0001.jpg', 'web-one');
        Storage::disk('s3')->put('wedding/one/hd/IMG_0001.jpg', 'hd-one');
        Storage::disk('s3')->put('wedding/one/hd/IMG_0002.jpg', 'hd-two');

        $collection = Collection::create([
            'name' => 'Photographer one',
            'slug' => 'photographer-one',
            'disk' => 's3',
            'web_prefix' => 'wedding/one/web',
            'hd_prefix' => 'wedding/one/hd',
        ]);

        $stats = app(MediaSyncService::class)->sync($collection);

        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['missing_web']);
        $this->assertSame(0, $stats['missing_hd']);
        $this->assertDatabaseHas('photos', ['filename' => 'IMG_0001.jpg', 'web_key' => 'wedding/one/web/IMG_0001.jpg', 'hd_key' => 'wedding/one/hd/IMG_0001.jpg']);
        $this->assertDatabaseCount('photos', 2);

        app(MediaSyncService::class)->sync($collection->fresh());
        $this->assertDatabaseCount('photos', 2);
    }
}
