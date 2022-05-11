<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Pagination\Paginator;

interface CommentCRUDServiceInterface
{
    public function getAllComments($postId = null, bool $canViewAll = false, int $page = 0, int $perPage = 100): Paginator;

    public function createComment(array $fields): Comment;

    public function updateComment(Comment $comment, array $fields): Comment;

    public function deleteComment(Comment $comment): ?bool;
}
