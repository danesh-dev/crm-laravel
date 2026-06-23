<?php

namespace App\Livewire\Customers;

use App\Services\Customer\CustomerService;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $email = '';

    public ?string $phone = null;

    public function save(CustomerService $service)
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:customers,email',
        ]);

        $service->create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        return redirect()->route('customers.index');
    }

    public function render()
    {
        return view('livewire.customers.create');
    }
}
