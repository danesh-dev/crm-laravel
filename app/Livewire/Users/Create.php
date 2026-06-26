<?php

namespace App\Livewire\Users;

use App\Services\User\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = 'Admin';

    public function save(UserService $service): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(UserService::ROLES)],
        ]);

        $service->create($data);

        session()->flash('success', __('User created successfully.'));

        $this->redirectRoute('users.index');
    }

    public function render(UserService $service): View
    {
        return view('livewire.users.create', [
            'roles' => $service->roles(),
        ]);
    }
}
