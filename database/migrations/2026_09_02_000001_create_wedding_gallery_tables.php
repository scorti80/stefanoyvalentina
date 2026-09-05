<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('disk')->default('s3');
            $table->string('web_prefix');
            $table->string('hd_prefix');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->string('web_key')->nullable();
            $table->string('hd_key')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('web_bytes')->nullable();
            $table->unsignedBigInteger('hd_bytes')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->string('checksum', 64)->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['collection_id', 'filename']);
        });

        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('share_token_hash', 64)->unique();
            $table->text('share_token');
            $table->string('pin_hash');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('allow_sd_download')->default(true);
            $table->boolean('allow_hd_download')->default(true);
            $table->boolean('allow_zip_download')->default(false);
            $table->timestamps();
        });

        Schema::create('photo_playlist', function (Blueprint $table) {
            $table->foreignId('playlist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['playlist_id', 'photo_id']);
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Our wedding film');
            $table->string('disk')->default('s3');
            $table->string('storage_key')->nullable();
            $table->string('poster_key')->nullable();
            $table->string('mime_type')->default('video/mp4');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
        Schema::dropIfExists('photo_playlist');
        Schema::dropIfExists('playlists');
        Schema::dropIfExists('photos');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('settings');
    }
};
