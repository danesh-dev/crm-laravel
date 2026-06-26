<?php

namespace App\Livewire\Automations;

use App\Models\Automation;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Show extends Component
{
    public int $automationId;

    public function mount(Automation $automation): void
    {
        $this->automationId = $automation->id;
    }

    public function render(): View
    {
        return view('livewire.automations.show', [
            'automation' => Automation::query()
                ->with([
                    'emailTemplate:id,name,subject',
                    'schedule',
                    'creator:id,name,email',
                ])
                ->findOrFail($this->automationId),
        ]);
    }
}
