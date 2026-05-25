<?php

namespace App\Services;

use App\Models\User;

class RolePermissionService
{
    private const ACCESS = [
        'dashboard' => ['admin', 'manager', 'cashier', 'waiter', 'kitchen', 'inventory'],
        'pos' => ['admin', 'manager', 'cashier', 'waiter'],
        'orders' => ['admin', 'manager', 'cashier', 'waiter'],
        'inventory' => ['admin', 'manager', 'inventory'],
        'tables' => ['admin', 'manager', 'waiter', 'cashier'],
        'kds' => ['admin', 'manager', 'kitchen'],
        'recipes' => ['admin', 'manager', 'inventory'],
        'purchases' => ['admin', 'manager', 'inventory'],
        'expenses' => ['admin', 'manager'],
        'credit' => ['admin', 'manager', 'cashier'],
        'reports' => ['admin', 'manager'],
        'shifts' => ['admin', 'manager', 'cashier'],
        'users' => ['admin'],
        'settings' => ['admin'],
        'hotel' => ['admin', 'manager'],
    ];

    public function allows(?User $user, string $ability): bool
    {
        if (! $user || ! $user->is_active) {
            return false;
        }

        return in_array((string) $user->role, self::ACCESS[$ability] ?? ['admin'], true);
    }

    public function authorize(?User $user, string $ability): void
    {
        abort_unless($this->allows($user, $ability), 403, 'You do not have permission to access this area.');
    }
}
