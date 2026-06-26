<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserService
{
    public const ROLES = ['Super Admin', 'Admin'];

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'email_verified_at' => now(),
            ]);

            $user->assignRole($data['role']);

            return $user;
        });
    }

    public function update(User $user, array $data, User $actor): User
    {
        return DB::transaction(function () use ($user, $data, $actor): User {
            $user->update(array_filter([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'] ?? null,
            ], fn ($value) => $value !== null));

            if ($actor->isNot($user)) {
                $this->ensureRoleChangeIsAllowed($user, $data['role']);
                $user->syncRoles([$data['role']]);
            }

            return $user->fresh();
        });
    }

    public function delete(User $user, User $actor): void
    {
        if ($actor->is($user)) {
            throw ValidationException::withMessages([
                'delete' => __('You cannot delete your own account.'),
            ]);
        }

        if ($user->hasRole('Super Admin') && $this->superAdminCount() <= 1) {
            throw ValidationException::withMessages([
                'delete' => __('You cannot delete the last Super Admin.'),
            ]);
        }

        $user->delete();
    }

    public function roles(): array
    {
        return collect(self::ROLES)
            ->each(fn (string $role) => Role::firstOrCreate(['name' => $role]))
            ->all();
    }

    private function ensureRoleChangeIsAllowed(User $user, string $role): void
    {
        if ($user->hasRole('Super Admin') && $role !== 'Super Admin' && $this->superAdminCount() <= 1) {
            throw ValidationException::withMessages([
                'role' => __('You cannot remove the last Super Admin.'),
            ]);
        }
    }

    private function superAdminCount(): int
    {
        return User::role('Super Admin')->count();
    }
}
