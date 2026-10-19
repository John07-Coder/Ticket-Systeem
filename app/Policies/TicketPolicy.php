<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->isStaff() || $ticket->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->role === 'admin' || $ticket->created_by === $user->id) {
            return true;
        }

        return $user->role === 'agent' && $ticket->assigned_to === $user->id;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->role === 'admin' || $ticket->created_by === $user->id;
    }
}
