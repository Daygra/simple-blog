<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use App\Services\PostCRUDServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class PostController extends Controller
{
    /**
     * @var $postCRUDService PostCRUDServiceInterface
     */
    private $postCRUDService;

    public function __construct(PostCRUDServiceInterface $postCRUDService)
    {
        $this->postCRUDService = $postCRUDService;
    }

    public function index(Request $request): JsonResponse
    {
        $page = $request->get('page', 0);
        $perPage = $request->get('perPage', 100);
        $posts = $this->postCRUDService->getAllPosts($page, $perPage);
        return response()->json(compact('posts'));
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->postCRUDService->createPost($request->validated());
        return response()->json($post);
    }

    public function show(Post $post): JsonResponse
    {
        return response()->json($post);
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        if (auth()->user()->cannot('update-post', $post)) {
            return response()->json(null, Response::HTTP_FORBIDDEN);
        }
        $post = $this->postCRUDService->updatePost($post, $request->validated());
        return response()->json($post);
    }

    public function destroy(Post $post): JsonResponse
    {
        if (auth()->user()->cannot('delete-post', $post)) {
            return response()->json(null, Response::HTTP_FORBIDDEN);
        }
        if ($this->postCRUDService->deletePost($post)) {
            return response()->json(null, Response::HTTP_OK);
        }
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
