<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user, Ticket $ticket): bool
    {
        return $user->isStaff() || $ticket->created_by === $user->id;
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $comment->user_id === $user->id;
    }
}
