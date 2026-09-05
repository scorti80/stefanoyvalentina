<?php

namespace Tests\Feature;

use App\Models\Playlist;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WeddingAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_is_locked_and_blocks_crawlers(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Enter the private PIN')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet, noimageindex, noai, noimageai');

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_site_pin_unlocks_home_and_video_remains_gated_without_it(): void
    {
        Setting::write('site_pin_hash', Hash::make('orchids'));

        $this->get('/watch')->assertRedirect('/');
        $this->post('/access', ['pin' => 'wrong'])->assertSessionHasErrors('pin');
        $this->post('/access', ['pin' => 'orchids'])->assertRedirect('/');
        $this->get('/')->assertOk()->assertSee('Thank you for being part of our story');
        $this->get('/watch')->assertOk();
    }

    public function test_playlist_requires_its_own_pin_and_unlocks_site_access(): void
    {
        $token = 'private-token-for-family';
        $playlist = Playlist::create([
            'name' => 'Family',
            'slug' => 'family',
            'share_token_hash' => hash('sha256', $token),
            'share_token' => $token,
            'pin_hash' => Hash::make('1948'),
            'is_active' => true,
        ]);

        $this->withSession(['wedding.site_revision' => 1])
            ->get("/photos/{$token}")
            ->assertOk()
            ->assertSee('Gallery PIN');

        $this->post("/photos/{$token}/access", ['pin' => '1948'])->assertRedirect("/photos/{$token}");
        $this->get("/photos/{$token}")->assertOk()->assertSee('This gallery is being prepared');
        $this->assertSame(1, session("wedding.playlists.{$playlist->id}"));
        $this->assertSame(1, session('wedding.site_revision'));
    }

    public function test_changing_the_site_pin_revokes_old_guest_sessions(): void
    {
        Setting::write('site_pin_hash', Hash::make('orchids'));
        Setting::write('site_pin_revision', 2);

        $this->withSession(['wedding.site_revision' => 1])
            ->get('/watch')
            ->assertRedirect('/');
    }
}
