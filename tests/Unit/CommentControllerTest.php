<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private $user;
    private $post;

    public function setup(): void
    {
        parent::setUp();
        $this->setUpFaker();

        $this->user = User::create([
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => "$2a$12$0PNIdKIVSMNMd5YwKjyuX.oWRAUaxZlT01VfkoAZxbC9b7vtm12QK", //322322
            'role' => User::ROLE_ADMIN,
        ]);
        $this->post = Post::create([
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'preview_text' => $this->faker->text,
            'detail_text' => $this->faker->text,
            'img_path' => $this->faker->filePath(),
            'user_id' => $this->user->id,
        ]);

    }

    public function test_action_index(): void
    {
        Comment::create([
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'is_moderated' => Comment::MODERATED,
            'post_id' => $this->post->id
        ]);
        Comment::create([
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'is_moderated' => Comment::BLOCKED,
            'post_id' => $this->post->id
        ]);
        $response = $this->get('/api/comments/', ['Accept' => 'application/json']);
        $response->assertStatus(200);
    }

    public function test_action_store(): void
    {
        $response = $this->post('/api/comments', [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'post_id' => $this->post->id,
        ], ['Accept' => 'application/json']);
        $response->assertStatus(200);
    }

    public function test_action_store_validation(): void
    {
        $response = $this->post('/api/comments', [
        ], ['Accept' => 'application/json']);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name'=>"The name field is required.",
            'email'=>"The email field is required.",
            'post_id'=>"The post id field is required."
        ]);
        $response = $this->post('/api/comments', [
            'name' => $this->faker->text(3000),
            'email' => $this->faker->name,
            'post_id' => 0,
        ], ['Accept' => 'application/json']);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name'=>"The name must not be greater than 255 characters.",
            'email'=>"The email must be a valid email address.",
            'post_id'=>"The selected post id is invalid."
        ]);
    }

    public function test_action_show(): void
    {
        $comment = Comment::create([
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'is_moderated' => Comment::MODERATED,
            'post_id' => $this->post->id
        ]);
        $response = $this->get("/api/comments/$comment->id");
        $response->assertStatus(200);

        $comment = Comment::create([
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'is_moderated' => Comment::BLOCKED,
            'post_id' => $this->post->id
        ]);
        $response = $this->get("/api/comments/$comment->id");
        $response->assertStatus(403);

        Auth::login($this->user);
        $comment = Comment::create([
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'is_moderated' => Comment::BLOCKED,
            'post_id' => $this->post->id
        ]);
        $response = $this->get("/api/comments/$comment->id");
        $response->assertStatus(200);
        Auth::logout();

        $user = User::create([
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => "$2a$12$0PNIdKIVSMNMd5YwKjyuX.oWRAUaxZlT01VfkoAZxbC9b7vtm12QK", //322322
            'role' => User::ROLE_USER,
        ]);
        $comment = Comment::create([
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'is_moderated' => Comment::BLOCKED,
            'post_id' => $this->post->id
        ]);
        $response = $this->get("/api/comments/$comment->id");
        $response->assertStatus(403);
        Auth::login($user);
        $comment = Comment::create([
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'is_moderated' => Comment::BLOCKED,
            'post_id' => $this->post->id,
            'user_id' => $user->id
        ]);
        $response = $this->get("/api/comments/$comment->id");
        $response->assertStatus(200);
    }

    public function test_action_update(): void
    {
        Auth::login($this->user);
        $comment = Comment::create([
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'is_moderated' => Comment::BLOCKED,
            'post_id' => $this->post->id
        ]);
        $response = $this->put("/api/comments/$comment->id", [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
        ], ['Accept' => 'application/json']);
        $response->assertStatus(200);
    }

    public function test_action_destroy(): void
    {
        Auth::login($this->user);
        $comment = Comment::create([
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'is_moderated' => Comment::BLOCKED,
            'post_id' => $this->post->id
        ]);
        $response = $this->delete("/api/comments/$comment->id", [],
            ['Accept' => 'application/json']);
        $response->assertStatus(200);
    }

    public function test_action_moderate(): void
    {
        Auth::login($this->user);
        $comment = Comment::create([
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'is_moderated' => Comment::BLOCKED,
            'post_id' => $this->post->id
        ]);
        $response = $this->put("/api/comments/moderate/$comment->id", [
            'is_moderated' => Comment::MODERATED,
        ], ['Accept' => 'application/json']);
        $response->assertStatus(200);
    }
}
