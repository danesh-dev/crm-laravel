<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Services\Customer\CustomerQuery;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $source = null;

    public ?string $subscription = null;

    public ?int $tag_id = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSource()
    {
        $this->resetPage();
    }

    public function updatingSubscription()
    {
        $this->resetPage();
    }

    public function updatingTagId()
    {
        $this->resetPage();
    }

    public function delete(int $customerId): void
    {
        Customer::query()->findOrFail($customerId)->delete();
    }

    public function render(CustomerQuery $query)
    {
        // dd(Customer::all());
        return view('livewire.customers.index', [
            'customers' => $query->apply([
                'search' => $this->search,
                'source' => $this->source,
                'subscription' => $this->subscription,
                'tag_id' => $this->tag_id,
            ])->paginate(15),
        ]);
    }
}
