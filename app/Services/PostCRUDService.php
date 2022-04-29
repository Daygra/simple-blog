<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class PostCRUDService implements PostCRUDServiceInterface
{
   /**
   * @var $imageService ImageServiceInterface
   */
   private $imageService;

    public function __construct(ImageServiceInterface $imageService)
    {
        $this->imageService = $imageService;
    }

    public function getAllPosts() :Collection
    {
        return Post::all();
    }

    public function createPost(array $fields): Post
    {
        $post = new Post();
        $post->fill($fields);
        $post->img_path = $this->imageService->saveImage(Post::IMAGE_STORAGE_PATH, $fields['img']);
        $post->user()->associate(auth()->user());
        $post->save();
        return $post;
    }

    public function updatePost(Post $post, array $fields): Post
    {
        $post->fill($fields);
        if(!$this->imageService->isImagesAreSame($this->imageService->getImgContentByPath($post->img_path), $fields['img']->get())) {
            $this->imageService->deleteImage($post->img_path);
            $post->img_path = $this->imageService->saveImage(Post::IMAGE_STORAGE_PATH, $fields['img']);
        }
        $post->save();
        return $post;
    }

    public function deletePost(Post $post): ?bool
    {
        $this->imageService->deleteImage($post->img_path);
        return $post->delete();
    }



}
