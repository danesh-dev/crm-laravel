<?php

namespace App\Livewire\Deliveries;

use App\Models\EmailDelivery;
use App\Services\Email\DeliveryActionService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Show extends Component
{
    public int $deliveryId;

    public ?string $message = null;

    public function mount(EmailDelivery $delivery): void
    {
        $this->deliveryId = $delivery->id;
    }

    public function render(): View
    {
        return view('livewire.deliveries.show', [
            'delivery' => EmailDelivery::query()
                ->with([
                    'customer',
                    'automation.emailTemplate',
                    'emailTemplate',
                    'batch',
                ])
                ->findOrFail($this->deliveryId),
        ]);
    }

    public function retry(DeliveryActionService $service): void
    {
        try {
            $service->retry(EmailDelivery::query()->findOrFail($this->deliveryId));

            session()->flash('success', __('Delivery queued for retry.'));
        } catch (ValidationException $exception) {
            $this->message = collect($exception->errors())->flatten()->first();
        }
    }

    public function cancel(DeliveryActionService $service): void
    {
        try {
            $service->cancel(EmailDelivery::query()->findOrFail($this->deliveryId));

            session()->flash('success', __('Delivery cancelled.'));
        } catch (ValidationException $exception) {
            $this->message = collect($exception->errors())->flatten()->first();
        }
    }
}
