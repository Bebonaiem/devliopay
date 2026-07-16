<?php

namespace App\Policies;

use App\Models\TicketThread;
use App\Models\User;

class TicketThreadPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TicketThread $ticket): bool
    {
        return $user->id === $ticket->user_id || $user->is_admin;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TicketThread $ticket): bool
    {
        return $user->id === $ticket->user_id || $user->is_admin;
    }

    public function delete(User $user, TicketThread $ticket): bool
    {
        return $user->is_admin;
    }
}
