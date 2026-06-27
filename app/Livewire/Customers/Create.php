<?php

namespace App\Livewire\Customers;

use App\Enums\UserSource;
use App\Models\Tag;
use App\Services\Customer\CustomerService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $email = '';

    public ?string $phone = null;

    /** @var array<int, int|string> */
    public array $tagIds = [];

    public function save(CustomerService $service)
    {
        $this->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:customers,email'],
            'phone' => ['nullable', 'string'],
            'tagIds' => ['array'],
            'tagIds.*' => ['integer', Rule::exists('tags', 'id')->whereNull('deleted_at')],
        ]);

        $service->create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => UserSource::MANUAL,
            'tag_ids' => $this->tagIds,
        ]);

        return redirect()->route('customers.index');
    }

    public function render()
    {
        return view('livewire.customers.create', [
            'tags' => Tag::query()
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
