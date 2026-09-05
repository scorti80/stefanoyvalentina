<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_pin' => ['required', 'string', 'min:4', 'max:100', 'confirmed'],
        ]);

        Setting::write('site_pin_hash', Hash::make($data['site_pin']));
        Setting::write('site_pin_revision', ((int) Setting::read('site_pin_revision', 1)) + 1);

        return back()->with('status', 'The site PIN was changed and previous guest sessions were revoked.');
    }
}
