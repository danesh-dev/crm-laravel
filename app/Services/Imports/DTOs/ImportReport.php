<?php

namespace App\Services\Imports\DTOs;

class ImportReport
{
    /** @var array<int, array{row: int, message: string}> */
    public array $errors = [];

    public int $processedRecords = 0;

    public int $customersCreated = 0;

    public int $customersUpdated = 0;

    public int $subscriptionsCreated = 0;

    public int $duplicateSubscriptionsSkipped = 0;

    public int $invalidRows = 0;

    public function __construct(
        public ?int $totalRecords = null,
        private float $startedAt = 0.0,
    ) {
        $this->startedAt = microtime(true);
    }

    public function addError(int $row, string $message): void
    {
        $this->invalidRows++;

        if (count($this->errors) < 200) {
            $this->errors[] = (new ImportError($row, $message))->toArray();
        }
    }

    public function processingTimeMs(): int
    {
        return (int) round((microtime(true) - $this->startedAt) * 1000);
    }

    public function toDatabaseValues(): array
    {
        return [
            'total_records' => $this->totalRecords,
            'processed_records' => $this->processedRecords,
            'customers_created' => $this->customersCreated,
            'customers_updated' => $this->customersUpdated,
            'subscriptions_created' => $this->subscriptionsCreated,
            'duplicate_subscriptions_skipped' => $this->duplicateSubscriptionsSkipped,
            'invalid_rows' => $this->invalidRows,
            'errors' => $this->errors,
            'processing_time_ms' => $this->processingTimeMs(),
        ];
    }
}
