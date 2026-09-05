<?php

namespace App\Services;

use App\Models\Playlist;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WeddingAccess
{
    public function verifySitePin(string $pin): bool
    {
        $storedHash = Setting::read('site_pin_hash');

        if ($storedHash) {
            return Hash::check($pin, $storedHash);
        }

        $configuredPin = (string) config('wedding.site_pin');

        return $configuredPin !== '' && hash_equals($configuredPin, $pin);
    }

    public function grantSite(Request $request): void
    {
        $request->session()->put('wedding.site_revision', $this->siteRevision());
        $request->session()->put('wedding.unlocked_at', now()->timestamp);
    }

    public function hasSiteAccess(Request $request): bool
    {
        return (int) $request->session()->get('wedding.site_revision') === $this->siteRevision();
    }

    public function grantPlaylist(Request $request, Playlist $playlist): void
    {
        $this->grantSite($request);
        $request->session()->put("wedding.playlists.{$playlist->id}", $playlist->access_revision);
    }

    public function canViewPlaylist(Request $request, Playlist $playlist): bool
    {
        return (int) $request->session()->get("wedding.playlists.{$playlist->id}") === $playlist->access_revision;
    }

    public function siteRevision(): int
    {
        return (int) Setting::read('site_pin_revision', 1);
    }
}
