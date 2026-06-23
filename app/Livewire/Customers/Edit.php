<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Services\Customer\CustomerService;
use Livewire\Component;

class Edit extends Component
{
    public Customer $customer;

    public string $name;

    public string $email;

    public ?string $phone = null;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;

        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
    }

    public function update(CustomerService $service): void
    {
        $this->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:customers,email,'.$this->customer->id],
            'phone' => ['nullable', 'string'],
        ]);

        $service->update($this->customer, [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        session()->flash('success', 'Customer updated successfully.');

        $this->redirectRoute('customers.index');
    }

    public function render()
    {
        return view('livewire.customers.edit');
    }
}
