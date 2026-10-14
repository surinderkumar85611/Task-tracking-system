<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class PruneOldNotifications extends Command
{
    protected $signature = 'notifications:prune';
    protected $description = 'Delete notifications older than 24 hours';

    public function handle(): int
    {
        $count = Notification::where('created_at', '<', now()->subDay())->delete();
        $this->info("Deleted {$count} notifications.");
        return self::SUCCESS;
    }
}