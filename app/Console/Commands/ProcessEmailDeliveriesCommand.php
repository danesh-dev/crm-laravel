<?php

namespace App\Console\Commands;

use App\Services\Email\EmailBatchDispatcher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;

#[Description('Dispatch pending email deliveries whose scheduled time has arrived')]
class ProcessEmailDeliveriesCommand extends Command
{
    protected $signature = 'email-deliveries:process {--limit=100 : Maximum batches to dispatch}';

    /**
     * Execute the console command.
     */
    public function handle(EmailBatchDispatcher $dispatcher): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $count = $dispatcher->dispatchDue($limit);

        $this->info("Dispatched {$count} due email delivery batches.");

        return self::SUCCESS;
    }
}
