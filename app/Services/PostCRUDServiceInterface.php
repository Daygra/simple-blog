<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

interface PostCRUDServiceInterface
{
    public function getAllPosts(int $page = 0, int $perPage = 100): Collection;

    public function createPost(array $fields): Post;

    public function updatePost(Post $post, array $fields): Post;

    public function deletePost(Post $post): ?bool;
}
