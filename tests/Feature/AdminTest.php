<?php

namespace Tests\Feature;

use App\Models\Playlist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
}
