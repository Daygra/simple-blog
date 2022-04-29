<?php

namespace App\Http\Requests\Comment;

class StoreCommentRequest  extends AbstractBaseCommentRequest
{

    public function rules(): array
    {
        return array_merge( $this->baseRules(),[
            'post_id' => 'required|exists:posts,id',
        ]);
    }
}
