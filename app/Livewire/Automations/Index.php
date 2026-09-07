<?php

namespace App\Livewire\Automations;

use App\Models\Automation;
use App\Services\Automation\AutomationService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(
        int $automationId,
        AutomationService $service
    ): void {

        $automation = Automation::query()
            ->findOrFail($automationId);

        $service->delete($automation);
    }

    public function render()
    {
        return view('livewire.automations.index', [
            'automations' => Automation::query()
                ->with([
                    'emailTemplate',
                    'schedule',
                ])
                ->when(
                    $this->search,
                    fn ($query) => $query->where(
                        'name',
                        'ilike',
                        "%{$this->search}%"
                    )
                )
                ->latest()
                ->paginate(15),
        ]);
    }
}
