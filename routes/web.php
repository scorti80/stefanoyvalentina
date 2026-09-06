<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlaylistController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\VideoController as AdminVideoController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', RobotsController::class)->name('robots');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/access', [HomeController::class, 'unlock'])->middleware('throttle:6,1')->name('site.unlock');
Route::post('/lock', [HomeController::class, 'lock'])->name('site.lock');
Route::get('/watch', VideoController::class)->middleware('site.access')->name('video');

Route::get('/photos/{token}', [GalleryController::class, 'show'])->name('gallery.show');
Route::post('/photos/{token}/access', [GalleryController::class, 'unlock'])->middleware('throttle:6,1')->name('gallery.unlock');
Route::get('/photos/{token}/download/{photo}/{variant}', [GalleryController::class, 'download'])->where('variant', 'sd|hd')->name('gallery.download');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
        Route::resource('collections', CollectionController::class)->except('show');
        Route::post('/collections/{collection}/sync', [CollectionController::class, 'sync'])->name('collections.sync');
        Route::resource('playlists', PlaylistController::class)->except('show');
        Route::get('/playlists/{playlist}/photos', [PlaylistController::class, 'photos'])->name('playlists.photos');
        Route::put('/playlists/{playlist}/photos', [PlaylistController::class, 'updatePhotos'])->name('playlists.photos.update');
        Route::post('/playlists/{playlist}/photos/select-all', [PlaylistController::class, 'selectAllPhotos'])->name('playlists.photos.select-all');
        Route::post('/playlists/{playlist}/rotate', [PlaylistController::class, 'rotate'])->name('playlists.rotate');
        Route::get('/video', [AdminVideoController::class, 'edit'])->name('video.edit');
        Route::put('/video', [AdminVideoController::class, 'update'])->name('video.update');
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
