<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determina si el usuario puede ver la lista de usuarios.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede ver un usuario específico.
     */
    public function view(User $user, User $model): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede crear usuarios.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede actualizar un usuario específico.
     */
    public function update(User $user, User $model): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede eliminar un usuario específico.
     */
    public function delete(User $user, User $model): bool
    {
        return true;
    }

    /**
     * (Opcional) Restaurar usuarios eliminados.
     */
    public function restore(User $user, User $model): bool
    {
        return true;
    }

    /**
     * (Opcional) Eliminar permanentemente usuarios.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return true;
    }
}
