<?php

namespace App\Livewire\Deliveries;

use App\Models\EmailDelivery;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Show extends Component
{
    public int $deliveryId;

    public function mount(EmailDelivery $delivery): void
    {
        $this->deliveryId = $delivery->id;
    }

    public function render(): View
    {
        return view('livewire.deliveries.show', [
            'delivery' => EmailDelivery::query()
                ->with([
                    'customer:id,name,email',
                    'automation:id,name,schedule_type,target_user_type',
                    'emailTemplate:id,name,subject',
                ])
                ->findOrFail($this->deliveryId),
        ]);
    }
}
