<?php

namespace App\Livewire\Deliveries;

use App\Models\EmailDelivery;
use App\Services\Email\DeliveryQuery;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $status = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function render(
        DeliveryQuery $query
    ) {
        return view(
            'livewire.deliveries.index',
            [
                'deliveries' => $query
                    ->apply([
                        'search' => $this->search,
                        'status' => $this->status,
                    ])
                    ->paginate(20),
            ]
        );
    }

    public function retry(
        int $deliveryId
    ): void {

        $delivery = EmailDelivery::findOrFail(
            $deliveryId
        );

        $delivery->update([
            'status' => 'pending',
            'failed_at' => null,
            'error_message' => null,
        ]);
    }
}
