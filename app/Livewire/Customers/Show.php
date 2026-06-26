<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Show extends Component
{
    public int $customerId;

    public function mount(Customer $customer): void
    {
        $this->customerId = $customer->id;
    }

    public function render(): View
    {
        return view('livewire.customers.show', [
            'customer' => Customer::query()
                ->with([
                    'latestSubscription',
                    'tags:id,name',
                ])
                ->findOrFail($this->customerId),
        ]);
    }
}
