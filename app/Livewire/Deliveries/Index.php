<?php

namespace App\Livewire\Deliveries;

use App\Models\Automation;
use App\Models\EmailDelivery;
use App\Models\EmailTemplate;
use App\Services\Email\DeliveryActionService;
use App\Services\Email\DeliveryQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $status = null;

    public ?string $automation_id = null;

    public ?string $email_template_id = null;

    public ?string $date_from = null;

    public ?string $date_to = null;

    public ?string $message = null;

    public function updating(string $property): void
    {
        if (in_array($property, [
            'search',
            'status',
            'automation_id',
            'email_template_id',
            'date_from',
            'date_to',
        ], true)) {
            $this->resetPage();
        }
    }

    public function render(
        DeliveryQuery $query
    ): View {
        return view(
            'livewire.deliveries.index',
            [
                'deliveries' => $query
                    ->apply($this->filters())
                    ->paginate(20),
                'failedDeliveryCount' => $query
                    ->failed($this->filters())
                    ->count(),
                'automations' => Automation::query()
                    ->select(['id', 'name'])
                    ->orderBy('name')
                    ->get(),
                'emailTemplates' => EmailTemplate::query()
                    ->select(['id', 'name'])
                    ->orderBy('name')
                    ->get(),
            ]
        );
    }

    public function retry(
        int $deliveryId,
        DeliveryActionService $service
    ): void {
        try {
            $service->retry(EmailDelivery::query()->findOrFail($deliveryId));

            session()->flash('success', __('Delivery queued for retry.'));
        } catch (ValidationException $exception) {
            $this->message = collect($exception->errors())->flatten()->first();
        }
    }

    public function cancel(
        int $deliveryId,
        DeliveryActionService $service
    ): void {
        try {
            $service->cancel(EmailDelivery::query()->findOrFail($deliveryId));

            session()->flash('success', __('Delivery cancelled.'));
        } catch (ValidationException $exception) {
            $this->message = collect($exception->errors())->flatten()->first();
        }
    }

    public function retryFilteredFailures(DeliveryActionService $service): void
    {
        $this->message = null;
        $result = $service->retryFilteredFailures($this->filters());

        session()->flash('success', __(
            ':queued queued for retry, :skipped skipped, :deferred deferred because of quota.',
            $result
        ));
    }

    private function filters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->status,
            'automation_id' => $this->automation_id,
            'email_template_id' => $this->email_template_id,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ];
    }
}
