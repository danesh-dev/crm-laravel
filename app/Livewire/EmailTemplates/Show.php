<?php

namespace App\Livewire\EmailTemplates;

use App\Models\EmailTemplate;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Show extends Component
{
    public int $templateId;

    public function mount(EmailTemplate $emailTemplate): void
    {
        $this->templateId = $emailTemplate->id;
    }

    public function render(): View
    {
        return view('livewire.email-templates.show', [
            'template' => EmailTemplate::query()
                ->with([
                    'creator:id,name,email',
                ])
                ->withCount([
                    'automations',
                    'emailDeliveries',
                ])
                ->findOrFail($this->templateId),
        ]);
    }
}
