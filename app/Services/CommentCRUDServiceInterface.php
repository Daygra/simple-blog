<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

interface CommentCRUDServiceInterface
{
    public function getAllComments(int $postId = null, bool $canViewAll = false): Collection;
    public function createComment(array $fields): Comment;
    public function updateComment(Comment $comment, array $fields): Comment;
    public function deleteComment(Comment $comment): ?bool;
}
