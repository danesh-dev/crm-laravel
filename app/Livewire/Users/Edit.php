<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public User $user;

    public string $name = '';

    public string $email = '';

    public ?string $password = null;

    public ?string $password_confirmation = null;

    public string $role = 'Admin';

    public function mount(User $user): void
    {
        $this->user = $user->load('roles');
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles->first()?->name ?? 'Admin';
    }

    public function save(UserService $service): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(UserService::ROLES)],
        ]);

        if (blank($data['password'])) {
            unset($data['password']);
        }

        $service->update($this->user, $data, auth()->user());

        session()->flash('success', __('User updated successfully.'));

        $this->redirectRoute('users.index');
    }

    public function render(UserService $service): View
    {
        return view('livewire.users.edit', [
            'roles' => $service->roles(),
            'canEditRole' => auth()->id() !== $this->user->id,
        ]);
    }
}
