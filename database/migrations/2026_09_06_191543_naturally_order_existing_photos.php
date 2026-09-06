<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            foreach (DB::table('collections')->pluck('id') as $collectionId) {
                $photos = DB::table('photos')->where('collection_id', $collectionId)
                    ->orderBy('id')->get(['id', 'filename'])
                    ->sortBy('filename', SORT_NATURAL | SORT_FLAG_CASE)->values();

                foreach ($photos as $position => $photo) {
                    DB::table('photos')->where('id', $photo->id)->update(['sort_order' => $position]);
                }
            }
        });
    }

    /**
     * Keep the corrected display order when rolling back; no schema changes were made.
     */
    public function down(): void {}
};
