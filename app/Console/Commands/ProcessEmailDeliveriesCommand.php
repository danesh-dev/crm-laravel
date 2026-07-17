<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailDeliveryJob;
use App\Models\EmailDelivery;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;

#[Description('Dispatch pending email deliveries whose scheduled time has arrived')]
class ProcessEmailDeliveriesCommand extends Command
{
    protected $signature = 'email-deliveries:process {--limit=100 : Maximum deliveries to dispatch}';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $deliveryIds = EmailDelivery::query()
            ->pending()
            ->due()
            ->orderBy('scheduled_for')
            ->limit($limit)
            ->pluck('id');

        $deliveryIds->each(
            fn (int $deliveryId) => SendEmailDeliveryJob::dispatch($deliveryId)
        );

        $this->info("Dispatched {$deliveryIds->count()} due email deliveries.");

        return self::SUCCESS;
    }
}
