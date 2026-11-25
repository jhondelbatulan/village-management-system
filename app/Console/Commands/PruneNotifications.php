<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class PruneNotifications extends Command
{
    protected $signature = 'notifications:prune';

    protected $description = 'Archive notifications older than one month and delete those older than two months';

    public function handle(): int
    {
        [$archived, $deleted] = Notification::prune();

        $this->info("Archived {$archived} notification(s), deleted {$deleted} notification(s).");

        return Command::SUCCESS;
    }
}
