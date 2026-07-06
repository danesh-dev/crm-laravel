<?php

namespace App\Livewire\Imports\Customers;

use App\Models\ImportRun;
use App\Services\Imports\ImportService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $file = null;

    public ?int $importRunId = null;

    public ?string $message = null;

    public function import(ImportService $imports): void
    {
        $this->validate([
            'file' => ['required', 'file', 'max:102400'],
        ]);

        $run = $imports->startCustomerImport($this->file, auth()->id());

        $this->importRunId = $run->id;
        $this->file = null;
        $this->message = $run->status === 'pending'
            ? __('Import queued. This page will update as it runs.')
            : __('Import finished.');
    }

    public function render(): View
    {
        return view('livewire.imports.customers.index', [
            'importRun' => $this->importRun(),
            'recentImports' => ImportRun::query()
                ->where('type', 'customers')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    private function importRun(): ?ImportRun
    {
        if ($this->importRunId !== null) {
            return ImportRun::query()->find($this->importRunId);
        }

        return ImportRun::query()
            ->where('type', 'customers')
            ->latest()
            ->first();
    }
}
