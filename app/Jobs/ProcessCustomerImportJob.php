<?php

namespace App\Jobs;

use App\Models\ImportRun;
use App\Services\Imports\CustomerJsonImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessCustomerImportJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        public int $importRunId,
    ) {}

    public function handle(CustomerJsonImportService $importer): void
    {
        $run = ImportRun::query()->findOrFail($this->importRunId);

        $importer->process($run);
    }

    public function failed(?Throwable $exception): void
    {
        ImportRun::query()
            ->whereKey($this->importRunId)
            ->update([
                'status' => 'failed',
                'finished_at' => now(),
            ]);

        Log::error('customer import job failed', [
            'import_run_id' => $this->importRunId,
            'exception' => $exception,
        ]);
    }
}
