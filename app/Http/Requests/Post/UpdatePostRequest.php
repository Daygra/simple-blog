<?php

namespace App\Http\Requests\Post;

class UpdatePostRequest extends AbstractBasePostRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'slug' => "required|max:255|unique:posts,slug,{$this->post->id}",
        ]);
    }
}
