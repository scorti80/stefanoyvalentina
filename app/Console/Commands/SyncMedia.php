<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Services\MediaSyncService;
use Illuminate\Console\Command;

class SyncMedia extends Command
{
    protected $signature = 'media:sync {collection? : Collection ID or slug}';

    protected $description = 'Index the configured photo collections from private storage';

    public function handle(MediaSyncService $service): int
    {
        $collections = Collection::query()
            ->when($this->argument('collection'), function ($query, $value) {
                $query->where(fn ($nested) => $nested->where('id', $value)->orWhere('slug', $value));
            })
            ->get();

        if ($collections->isEmpty()) {
            $this->error('No matching collections were found.');

            return self::FAILURE;
        }

        foreach ($collections as $collection) {
            $this->components->task("Syncing {$collection->name}", function () use ($service, $collection) {
                $stats = $service->sync($collection);
                $this->newLine();
                $this->line("  {$stats['total']} photos · {$stats['missing_web']} missing web · {$stats['missing_hd']} missing HD");
            });
        }

        return self::SUCCESS;
    }
}
