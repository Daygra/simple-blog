<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

class CommentCRUDService implements CommentCRUDServiceInterface
{
    public function getAllComments( $postId = null, bool $canViewAll = false, int $page = 0, int $perPage = 100): Paginator
    {
        $query = Comment::when($postId, function (Builder $query) use ($postId) {
            $query->where('post_id', $postId);
        })->when(!$canViewAll, function (Builder $query) {
            $query->where('is_moderated', Comment::MODERATED)
                ->orWhere(function (Builder $query) {
                    $query->whereNotNull('user_id')
                        ->where('user_id', auth()->id());
                });
        });
        return $query->simplePaginate($perPage, ['*'],'page', $page);
    }

    public function createComment(array $fields): Comment
    {
        $comment = new Comment();
        $comment->fill($fields);
        $comment->user()->associate(auth()->user());
        $comment->save();
        return $comment;
    }

    public function updateComment(Comment $comment, array $fields): Comment
    {
        $comment->fill($fields);
        $comment->save();
        return $comment;
    }

    public function deleteComment(Comment $comment): ?bool
    {
        return $comment->delete();
    }
}
