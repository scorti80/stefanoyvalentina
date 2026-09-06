<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Playlist;
use App\Models\User;
use App\Services\MediaSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_uses_natural_filename_order_for_existing_and_new_photos(): void
    {
        Storage::fake('s3');
        $collection = Collection::create([
            'name' => 'Photographer', 'slug' => 'photographer', 'disk' => 's3',
            'web_prefix' => 'web', 'hd_prefix' => 'hd',
        ]);
        foreach (['pic-278.jpg', 'pic-279.jpg', 'pic-28.jpg', 'pic-280.jpg'] as $position => $filename) {
            $collection->photos()->create(['filename' => $filename, 'sort_order' => $position]);
            Storage::disk('s3')->put('hd/'.$filename, 'photo');
        }
        Storage::disk('s3')->put('web/pic-2.jpg', 'photo');
        Storage::disk('s3')->put('hd/pic-10.jpg', 'photo');

        app(MediaSyncService::class)->sync($collection);

        $this->assertSame(
            ['pic-2.jpg', 'pic-10.jpg', 'pic-28.jpg', 'pic-278.jpg', 'pic-279.jpg', 'pic-280.jpg'],
            $collection->photos()->orderBy('sort_order')->pluck('filename')->all()
        );
        $this->assertDatabaseCount('photos', 6);
    }

    public function test_existing_photo_order_is_corrected_in_admin_and_shared_gallery(): void
    {
        $collection = Collection::create([
            'name' => 'Photographer', 'slug' => 'photographer', 'web_prefix' => 'web', 'hd_prefix' => 'hd',
        ]);
        $playlist = Playlist::create([
            'name' => 'Family', 'slug' => 'family', 'share_token' => 'family-token',
            'share_token_hash' => hash('sha256', 'family-token'), 'pin_hash' => 'unused',
        ]);
        foreach (['pic-278.jpg', 'pic-279.jpg', 'pic-28.jpg', 'pic-280.jpg'] as $position => $filename) {
            $photo = $collection->photos()->create(['filename' => $filename, 'sort_order' => $position]);
            $playlist->photos()->attach($photo, ['position' => $photo->id]);
        }

        $migration = require database_path('migrations/2026_09_06_191543_naturally_order_existing_photos.php');
        $migration->up();

        $expected = ['pic-28.jpg', 'pic-278.jpg', 'pic-279.jpg', 'pic-280.jpg'];
        $this->assertSame($expected, $collection->photos()->orderBy('sort_order')->pluck('filename')->all());
        $this->actingAs(User::factory()->create())
            ->get(route('admin.playlists.photos', $playlist))->assertSeeInOrder($expected);
        $this->withSession(['wedding.playlists.'.$playlist->id => 1])
            ->get(route('gallery.show', 'family-token'))->assertSeeInOrder($expected);
        $this->assertSame(4, $playlist->photos()->count());
    }

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
