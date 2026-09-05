<?php

namespace App\Jobs;

use App\Models\Collection;
use App\Services\MediaSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncCollectionMedia implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public function __construct(public Collection $collection) {}

    public function handle(MediaSyncService $service): void
    {
        $service->sync($this->collection->fresh());
    }
}
