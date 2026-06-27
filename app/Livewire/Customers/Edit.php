<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Tag;
use App\Services\Customer\CustomerService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public Customer $customer;

    public string $name;

    public string $email;

    public ?string $phone = null;

    /** @var array<int, int|string> */
    public array $tagIds = [];

    public function mount(Customer $customer): void
    {
        $this->customer = $customer->load('tags:id');

        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->tagIds = $this->customer->tags
            ->pluck('id')
            ->all();
    }

    public function update(CustomerService $service): void
    {
        $this->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:customers,email,'.$this->customer->id],
            'phone' => ['nullable', 'string'],
            'tagIds' => ['array'],
            'tagIds.*' => ['integer', Rule::exists('tags', 'id')->whereNull('deleted_at')],
        ]);

        $service->update($this->customer, [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'tag_ids' => $this->tagIds,
        ]);

        session()->flash('success', 'Customer updated successfully.');

        $this->redirectRoute('customers.index');
    }

    public function render()
    {
        return view('livewire.customers.edit', [
            'tags' => Tag::query()
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
