<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\CommentModerateRequest;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\User;
use App\Services\CommentCRUDServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    private $commentCRUDService;

    public function __construct(CommentCRUDServiceInterface $commentCRUDService)
    {
        $this->commentCRUDService = $commentCRUDService;
    }

    public function index(Request $request): JsonResponse
    {
        $canViewAll = auth()->user() ? auth()->user()->can('view-all-comments', Comment::class) : false;
        $postId = $request->get('postId', null);
        $page = $request->get('page', 0);
        $perPage = $request->get('perPage', 100);
        $comments = $this->commentCRUDService->getAllComments($postId, $canViewAll, $page, $perPage);
        return response()->json(compact('comments'));
    }

    public function store(StoreCommentRequest $request): JsonResponse
    {
        $post = $this->commentCRUDService->createComment($request->validated());
        return response()->json($post);
    }

    public function show(Comment $comment)
    {
        $user = auth()->user() ?? new User();
        if ($user->cant('view-comment', $comment)) {
            return response()->noContent(Response::HTTP_FORBIDDEN);
        }
        return response()->json($comment);
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        if (auth()->user()->cannot('update-comment', $comment)) {
            return response()->noContent(Response::HTTP_FORBIDDEN);
        }
        $post = $this->commentCRUDService->updateComment($comment, $request->validated());
        return response()->json($post);
    }

    public function moderate(CommentModerateRequest $request, Comment $comment)
    {
        if (auth()->user()->cannot('moderate-comment', $comment)) {
            return response()->noContent(Response::HTTP_FORBIDDEN);
        }
        $post = $this->commentCRUDService->updateComment($comment, $request->safe()->only('is_moderated'));
        return response()->json($post);
    }

    public function destroy(Comment $comment): Response
    {
        if (auth()->user()->cannot('delete-comment', $comment)) {
            return response()->noContent(Response::HTTP_FORBIDDEN);
        }
        if ($this->commentCRUDService->deleteComment($comment)) {
            return response()->noContent(Response::HTTP_OK);
        }
        return response()->noContent(Response::HTTP_NO_CONTENT);
    }
}
