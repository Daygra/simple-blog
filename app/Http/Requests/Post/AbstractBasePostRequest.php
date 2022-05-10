<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractBasePostRequest extends FormRequest
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
            'title' => 'required|max:255',
            'slug' => 'required|max:255|unique:posts,slug',
            'preview_text' => 'required|max:255',
            'detail_text' => 'required|max:65535',
            'img' => 'required|image',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules(): array;

}
