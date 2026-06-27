<?php

namespace App\Livewire\EmailTemplates;

use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Services\Email\TemplatePreviewService;
use App\Services\EmailTemplate\EmailTemplateService;
use Livewire\Component;

class Edit extends Component
{
    public EmailTemplate $template;

    public string $name;

    public string $subject;

    public string $body_html;

    public ?string $preview = null;

    public ?int $previewCustomerId = null;

    public function mount(
        EmailTemplate $emailTemplate
    ): void {

        $this->template = $emailTemplate;

        $this->name = $emailTemplate->name;
        $this->subject = $emailTemplate->subject;
        $this->body_html = $emailTemplate->body_html;

        $this->previewCustomerId =
            Customer::query()->value('id');
    }

    public function generatePreview(
        TemplatePreviewService $previewService
    ): void {

        $customer = Customer::find(
            $this->previewCustomerId
        );

        if (! $customer) {
            return;
        }

        $this->preview = $previewService->preview(
            $this->body_html,
            $customer
        );
    }

    public function save(
        EmailTemplateService $service
    ): void {

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string'],
        ]);

        $service->update(
            $this->template,
            [
                'name' => $this->name,
                'subject' => $this->subject,
                'body_html' => $this->body_html,
            ]
        );

        session()->flash(
            'success',
            'Template updated successfully.'
        );
    }

    public function render()
    {
        return view(
            'livewire.email-templates.edit',
            [
                'customers' => Customer::query()
                    ->orderBy('name')
                    ->get(),
            ]
        );
    }
}
