<?php

namespace App\Http\Requests\Post;

class StorePostRequest extends AbstractBasePostRequest
{
    public function rules(): array
    {
        return $this->baseRules();
    }
}
