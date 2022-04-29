<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;


    public function viewAllComments(User $user)
    {
        return $user->isAdmin();
    }
    public function viewComment(User $user, Comment $comment)
    {
        return $user->isAdmin() || ($comment->user_id === $user->id) || ($comment->is_moderated === Comment::MODERATED);
    }

    public function updateComment(User $user, Comment $comment)
    {
        return $user->isAdmin() ||
            ($comment->user_id === $user->id && $comment->is_moderated === Comment::BLOCKED );
    }
    public function moderateComment(User $user, Comment $comment)
    {
        return $user->isAdmin() || ($comment->post->user_id === $user->id);
    }

    public function deleteComment(User $user, Comment $comment)
    {
        return $user->isAdmin() || ($comment->user_id === $user->id);
    }


}
