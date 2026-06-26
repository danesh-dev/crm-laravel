<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Services\Customer\CustomerDetailsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public int $customerId;

    public function mount(Customer $customer): void
    {
        $this->customerId = $customer->id;
    }

    public function render(CustomerDetailsService $details): View
    {
        $customer = Customer::query()
            ->with([
                'tags:id,name',
            ])
            ->findOrFail($this->customerId);

        return view('livewire.customers.show', [
            'customer' => $customer,
            'statistics' => $details->statistics($customer),
            'subscriptionHistory' => $details->subscriptionHistory($customer),
            'emailHistory' => $details->emailHistory($customer),
            'upcomingEmails' => $details->upcomingEmails($customer),
            'timeline' => $details->timeline($customer),
        ]);
    }
}
