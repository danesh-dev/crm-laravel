<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $message = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $userId, UserService $service): void
    {
        try {
            $service->delete(User::query()->findOrFail($userId), auth()->user());
            session()->flash('success', __('User deleted successfully.'));
        } catch (ValidationException $exception) {
            $this->message = collect($exception->errors())->flatten()->first();
        }
    }

    public function render(): View
    {
        return view('livewire.users.index', [
            'users' => User::query()
                ->with('roles:id,name')
                ->when($this->search, function ($query): void {
                    $query->where(function ($query): void {
                        $query
                            ->where('name', 'ilike', "%{$this->search}%")
                            ->orWhere('email', 'ilike', "%{$this->search}%");
                    });
                })
                ->latest()
                ->paginate(15),
        ]);
    }
}
