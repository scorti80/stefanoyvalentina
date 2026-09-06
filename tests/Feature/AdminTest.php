<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_require_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_can_create_a_protected_playlist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/playlists', [
            'name' => 'Close family',
            'description' => 'Our favorite moments',
            'pin' => '4826',
            'is_active' => '1',
            'allow_sd_download' => '1',
            'allow_hd_download' => '1',
        ]);

        $playlist = Playlist::firstOrFail();
        $response->assertRedirect(route('admin.playlists.photos', $playlist));
        $this->assertTrue(Hash::check('4826', $playlist->pin_hash));
        $this->assertNotSame('4826', $playlist->share_token);
        $this->assertSame(hash('sha256', $playlist->share_token), $playlist->share_token_hash);
    }

    public function test_primary_admin_screens_render(): void
    {
        $user = User::factory()->create();

        foreach (['/admin', '/admin/collections', '/admin/collections/create', '/admin/playlists', '/admin/playlists/create', '/admin/video', '/admin/settings'] as $path) {
            $this->actingAs($user)->get($path)->assertOk();
        }
    }

    public function test_admin_can_select_all_photos_across_pages_and_collections(): void
    {
        $playlist = $this->createPlaylist();
        $collections = collect(['first', 'second'])->map(fn ($name) => Collection::create([
            'name' => $name, 'slug' => $name, 'web_prefix' => $name.'/web', 'hd_prefix' => $name.'/hd',
        ]));
        for ($index = 0; $index < 1000; $index++) {
            Photo::create(['collection_id' => $collections[$index % 2]->id, 'filename' => $index.'.jpg']);
        }
        $inactive = Photo::create(['collection_id' => $collections[0]->id, 'filename' => 'inactive.jpg', 'is_active' => false]);
        $existing = Photo::firstOrFail();
        $playlist->photos()->attach($existing, ['position' => 2000]);
        $url = route('admin.playlists.photos', $playlist);
        $this->actingAs(User::factory()->create())->get($url)
            ->assertSee('Select All')->assertSee(route('admin.playlists.photos.select-all', $playlist));

        $response = $this->from($url)->post(route('admin.playlists.photos.select-all', $playlist));

        $response->assertRedirect($url)->assertSessionHas('status', 'All 1000 photographs matching the current filters are now included in this playlist.');
        $this->assertSame(1000, $playlist->photos()->count());
        $this->assertDatabaseMissing('photo_playlist', ['playlist_id' => $playlist->id, 'photo_id' => $inactive->id]);
        $this->assertDatabaseHas('photo_playlist', ['playlist_id' => $playlist->id, 'photo_id' => $existing->id, 'position' => 2000]);

        $this->post(route('admin.playlists.photos.select-all', $playlist))->assertRedirect();
        $this->assertSame(1000, $playlist->photos()->count());
    }

    public function test_select_all_requires_authentication(): void
    {
        $playlist = $this->createPlaylist();

        $this->post(route('admin.playlists.photos.select-all', $playlist))->assertRedirect('/admin/login');

        $this->assertDatabaseCount('photo_playlist', 0);
    }

    public function test_select_all_handles_an_empty_photo_library(): void
    {
        $playlist = $this->createPlaylist();

        $this->actingAs(User::factory()->create())->post(route('admin.playlists.photos.select-all', $playlist))
            ->assertRedirect()->assertSessionHas('status', 'All 0 photographs matching the current filters are now included in this playlist.');

        $this->assertDatabaseCount('photo_playlist', 0);
    }

    #[DataProvider('photoFilters')]
    public function test_select_all_respects_current_filters_across_pages(bool $filterCollection, string $search, int $expectedCount): void
    {
        $playlist = $this->createPlaylist();
        $collections = collect(['first', 'second'])->map(fn ($name) => Collection::create([
            'name' => $name, 'slug' => $name, 'web_prefix' => $name.'/web', 'hd_prefix' => $name.'/hd',
        ]));
        foreach ($collections as $collection) {
            for ($index = 0; $index < 60; $index++) {
                Photo::create(['collection_id' => $collection->id, 'filename' => 'ceremony-'.$index.'.jpg']);
            }
            Photo::create(['collection_id' => $collection->id, 'filename' => 'party.jpg']);
            Photo::create(['collection_id' => $collection->id, 'filename' => 'ceremony-inactive.jpg', 'is_active' => false]);
        }
        $filters = ['collection' => $filterCollection ? $collections[0]->id : '', 'search' => $search];
        $url = route('admin.playlists.photos', [$playlist, ...$filters]);
        $this->actingAs(User::factory()->create())->get($url)
            ->assertSee('name="collection" value="'.$filters['collection'].'"', false)
            ->assertSee('name="search" value="'.$search.'"', false)
            ->assertSee('Immediately saves all '.$expectedCount.' photos matching the current filters');

        $response = $this->from($url)->post(route('admin.playlists.photos.select-all', $playlist), $filters);

        $response->assertRedirect($url)->assertSessionHas('status', 'All '.$expectedCount.' photographs matching the current filters are now included in this playlist.');
        $this->assertSame($expectedCount, $playlist->photos()->count());
        $this->assertSame(0, $playlist->photos()->where('is_active', false)->count());
        if ($filterCollection) {
            $this->assertSame(0, $playlist->photos()->where('collection_id', $collections[1]->id)->count());
        }
        if ($search === 'ceremony') {
            $this->assertSame(0, $playlist->photos()->where('filename', 'party.jpg')->count());
        }
    }

    public static function photoFilters(): array
    {
        return [
            'collection' => [true, '', 61],
            'filename' => [false, 'ceremony', 120],
            'collection and filename' => [true, 'ceremony', 60],
            'no matches' => [true, 'missing', 0],
        ];
    }

    private function createPlaylist(): Playlist
    {
        return Playlist::create([
            'name' => 'Family', 'slug' => 'family', 'share_token' => 'family-token',
            'share_token_hash' => hash('sha256', 'family-token'), 'pin_hash' => Hash::make('4826'),
        ]);
    }
}
