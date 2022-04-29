<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractBaseCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function baseRules(): array
    {
        return [
            'name' => 'required|max:255',
            'email' => 'required|email',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
   abstract public function rules(): array;

}
