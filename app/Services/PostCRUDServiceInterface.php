<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Pagination\Paginator;

interface PostCRUDServiceInterface
{
    public function getAllPosts(int $page = 0, int $perPage = 100): Paginator;

    public function createPost(array $fields): Post;

    public function updatePost(Post $post, array $fields): Post;

    public function deletePost(Post $post): ?bool;
}
