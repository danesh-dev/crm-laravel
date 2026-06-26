<?php

namespace App\Livewire\Automations;

use App\Models\Automation;
use App\Services\Automation\AutomationDetailsService;
use App\Services\Automation\AutomationService;
use App\Services\Automation\AutomationStatisticsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Show extends Component
{
    public int $automationId;

    public ?string $message = null;

    public function mount(Automation $automation): void
    {
        $this->automationId = $automation->id;
    }

    public function render(
        AutomationDetailsService $details,
        AutomationStatisticsService $statistics,
    ): View {
        $automation = $this->automation();
        $stats = $statistics->get($automation);

        return view('livewire.automations.show', [
            'automation' => $automation,
            'statistics' => $stats['summary'],
            'dailyChart' => $stats['daily'],
            'recentDeliveries' => $details->recentDeliveries($automation),
        ]);
    }

    public function runNow(
        AutomationService $service,
        AutomationStatisticsService $statistics,
    ): void {
        $automation = $this->automation();
        $queued = $service->runNow($automation);
        $statistics->forget($automation);

        session()->flash('success', trans_choice(
            ':count email queued.|:count emails queued.',
            $queued
        ));
    }

    public function duplicate(AutomationService $service): void
    {
        $copy = $service->duplicate($this->automation(), auth()->id());

        $this->redirectRoute('automations.show', $copy, navigate: true);
    }

    public function pause(
        AutomationService $service,
        AutomationStatisticsService $statistics,
    ): void {
        $automation = $this->automation();
        $service->pause($automation);
        $statistics->forget($automation);

        session()->flash('success', __('Automation paused.'));
    }

    public function resume(
        AutomationService $service,
        AutomationStatisticsService $statistics,
    ): void {
        $automation = $this->automation();
        $service->resume($automation);
        $statistics->forget($automation);

        session()->flash('success', __('Automation resumed.'));
    }

    public function delete(
        AutomationService $service,
        AutomationStatisticsService $statistics,
    ): void {
        $automation = $this->automation();
        $statistics->forget($automation);
        $service->delete($automation);

        $this->redirectRoute('automations.index', navigate: true);
    }

    private function automation(): Automation
    {
        return Automation::query()
            ->with([
                'emailTemplate:id,name,subject',
                'schedule',
                'creator:id,name,email',
            ])
            ->findOrFail($this->automationId);
    }
}
