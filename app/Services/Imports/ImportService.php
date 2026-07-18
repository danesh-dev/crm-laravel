<?php

namespace App\Services\Imports;

use App\Jobs\ProcessCustomerImportJob;
use App\Models\ImportRun;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ImportService
{
    private const QUEUE_THRESHOLD = 1000;

    public function __construct(
        private JsonRecordReader $reader,
        private CustomerJsonImportService $customerImporter,
    ) {}

    public function startCustomerImport(UploadedFile $file, ?int $userId = null): ImportRun
    {
        $path = $file->storeAs(
            'imports/customers',
            Str::uuid().'.json',
        );

        $run = ImportRun::query()->create([
            'type' => 'customers',
            'status' => 'pending',
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'created_by' => $userId,
        ]);

        Log::info('customer import file received', [
            'import_run_id' => $run->id,
            'original_filename' => $run->original_filename,
        ]);

        try {
            $totalRecords = $this->reader->count($path);
        } catch (Throwable $exception) {
            $run->update([
                'status' => 'failed',
                'invalid_rows' => 1,
                'errors' => [
                    [
                        'row' => 0,
                        'message' => $exception->getMessage(),
                    ],
                ],
                'finished_at' => now(),
            ]);

            Log::error('customer import json validation failed', [
                'import_run_id' => $run->id,
                'exception' => $exception,
            ]);

            return $run->refresh();
        }

        $run->update(['total_records' => $totalRecords]);

        if ($totalRecords > self::QUEUE_THRESHOLD) {
            ProcessCustomerImportJob::dispatch($run->id);

            return $run->refresh();
        }

        $this->customerImporter->process($run->refresh());

        return $run->refresh();
    }
}
