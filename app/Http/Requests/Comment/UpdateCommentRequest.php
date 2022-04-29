<?php

namespace App\Http\Requests\Comment;

class UpdateCommentRequest  extends AbstractBaseCommentRequest
{
    public function rules(): array
    {
        return $this->baseRules();
    }
}
