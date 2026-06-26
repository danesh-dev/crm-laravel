<?php

namespace App\Livewire\Automations;

use App\Models\Automation;
use App\Services\Automation\AutomationDetailsService;
use App\Services\Automation\AutomationService;
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

    public function render(AutomationDetailsService $details): View
    {
        $automation = $this->automation();

        return view('livewire.automations.show', [
            'automation' => $automation,
            'statistics' => $details->statistics($automation),
            'recentDeliveries' => $details->recentDeliveries($automation),
        ]);
    }

    public function runNow(AutomationService $service): void
    {
        $queued = $service->runNow($this->automation());

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

    public function pause(AutomationService $service): void
    {
        $service->pause($this->automation());

        session()->flash('success', __('Automation paused.'));
    }

    public function resume(AutomationService $service): void
    {
        $service->resume($this->automation());

        session()->flash('success', __('Automation resumed.'));
    }

    public function delete(AutomationService $service): void
    {
        $service->delete($this->automation());

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
