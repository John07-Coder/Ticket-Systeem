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
        if ($user->role === 'admin') {
            return true;
        }

        return $ticket->created_by === $user->id;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }
}
