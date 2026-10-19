<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function update(User $user, Comment $comment): bool
    {
        if ($comment->is_internal && ! $user->isStaff()) {
            return false;
        }

        return $user->isStaff() || $comment->user_id === $user->id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $this->update($user, $comment);
    }
}
