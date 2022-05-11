<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'preview_text' => $this->faker->text,
            'detail_text' => $this->faker->text,
            'img_path' => Storage::disk('public')->putFile( Post::IMAGE_DIRECTORY, $this->faker->image),
            'user_id' => User::factory(),
        ];
    }
}
