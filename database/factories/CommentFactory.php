<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
                'name' => $this->faker->name,
                'email' => $this->faker->email,
                'is_moderated' => Comment::BLOCKED,
                'post_id' => Comment::factory()
        ];
    }

    public function moderated()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_moderated' => Comment::MODERATED,
            ];
        });
    }
}
