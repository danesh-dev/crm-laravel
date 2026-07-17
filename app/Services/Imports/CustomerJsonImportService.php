<?php

namespace App\Services\Imports;

use App\Models\ImportRun;
use App\Services\Imports\DTOs\ImportReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CustomerJsonImportService
{
    public function __construct(
        private JsonRecordReader $reader,
        private JsonImportValidator $validator,
        private CustomerImportService $customers,
        private SubscriptionImportService $subscriptions,
    ) {}

    public function process(ImportRun $run): ImportReport
    {
        $report = new ImportReport($run->total_records);

        Log::info('customer import started', [
            'import_run_id' => $run->id,
            'file_path' => $run->file_path,
        ]);

        $run->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            foreach ($this->reader->records($run->file_path) as $rowNumber => $row) {
                $report->processedRecords++;

                $validated = $this->validator->validate($row);

                if ($validated['record'] === null) {
                    $message = implode(' ', $validated['errors']);
                    $report->addError($rowNumber, $message);
                    Log::warning('customer import invalid row', [
                        'import_run_id' => $run->id,
                        'row' => $rowNumber,
                        'errors' => $validated['errors'],
                    ]);
                    $this->checkpoint($run, $report);

                    continue;
                }

                try {
                    DB::transaction(function () use ($validated, $report): void {
                        $customerResult = $this->customers->import($validated['record']);

                        if ($customerResult['created']) {
                            $report->customersCreated++;
                        }

                        if ($customerResult['updated']) {
                            $report->customersUpdated++;
                        }

                        $subscriptionResult = $this->subscriptions->import(
                            $customerResult['customer'],
                            $validated['record'],
                        );

                        if ($subscriptionResult['created']) {
                            $report->subscriptionsCreated++;
                        }

                        if ($subscriptionResult['duplicate']) {
                            $report->duplicateSubscriptionsSkipped++;
                        }
                    });
                } catch (Throwable $exception) {
                    $report->addError($rowNumber, __('Unexpected row import failure.'));
                    Log::error('customer import failed row', [
                        'import_run_id' => $run->id,
                        'row' => $rowNumber,
                        'exception' => $exception,
                    ]);
                }

                $this->checkpoint($run, $report);
            }

            $run->update([
                ...$report->toDatabaseValues(),
                'status' => 'finished',
                'finished_at' => now(),
            ]);

            Log::info('customer import finished', [
                'import_run_id' => $run->id,
                'report' => $report->toDatabaseValues(),
            ]);

            return $report;
        } catch (Throwable $exception) {
            $run->update([
                ...$report->toDatabaseValues(),
                'status' => 'failed',
                'finished_at' => now(),
            ]);

            Log::error('customer import failed', [
                'import_run_id' => $run->id,
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    private function checkpoint(ImportRun $run, ImportReport $report): void
    {
        if ($report->processedRecords % 100 !== 0) {
            return;
        }

        $run->update($report->toDatabaseValues());
    }
}
