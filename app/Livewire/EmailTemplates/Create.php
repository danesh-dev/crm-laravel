<?php

namespace App\Livewire\EmailTemplates;

use App\Models\Customer;
use App\Services\Email\TemplatePreviewService;
use App\Services\EmailTemplate\EmailTemplateService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $subject = '';

    public string $body_html = '';

    public ?string $preview = null;

    public ?int $previewCustomerId = null;

    public function generatePreview(
        TemplatePreviewService $previewService
    ): void {

        $customer = Customer::find($this->previewCustomerId);

        if (! $customer) {
            session()->flash('error', 'Customer not found.');

            return;
        }

        $this->preview = $previewService->preview(
            $this->body_html,
            $customer
        );

        logger()->info('preview generated', [
            'preview' => $this->preview,
        ]);
    }

    public function save(
        EmailTemplateService $service
    ): void {

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string'],
        ]);

        $service->create([
            'name' => $this->name,
            'subject' => $this->subject,
            'body_html' => $this->body_html,
            'created_by' => Auth::id(),
        ]);

        $this->redirectRoute(
            'templates.index',
            navigate: true,
        );
    }

    public function render()
    {
        return view(
            'livewire.email-templates.create', [
                'customers' => Customer::query()
                    ->orderBy('name')
                    ->get(),
            ]
        );
    }

    public function mount(): void
    {
        $this->previewCustomerId = Customer::query()->value('id');
    }
}
