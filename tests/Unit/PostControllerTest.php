<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private $user;

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

    }

    public function test_action_index(): void
    {
        Post::create([
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'preview_text' => $this->faker->text,
            'detail_text' => $this->faker->text,
            'img_path' => $this->faker->filePath(),
            'user_id' => $this->user->id,
        ]);
        $response = $this->get('/api/posts/');
        $response->assertStatus(200);
    }

    public function test_action_store(): void
    {
        Auth::login($this->user);
        $response = $this->post('/api/posts', [
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'preview_text' => $this->faker->text,
            'detail_text' => $this->faker->text,
            'img' => UploadedFile::fake()->image('file.png', 600, 600),

        ], ['Accept' => 'application/json']);
        $response->assertStatus(200);

    }

    public function test_action_show(): void
    {
        $post = Post::create([
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'preview_text' => $this->faker->text,
            'detail_text' => $this->faker->text,
            'img_path' => $this->faker->filePath(),
            'user_id' => $this->user->id,
        ]);

        $response = $this->get("/api/posts/$post->id");
        $response->assertStatus(200);
    }

    public function test_action_update(): void
    {
        Auth::login($this->user);
        $post = Post::create([
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'preview_text' => $this->faker->text,
            'detail_text' => $this->faker->text,
            'img_path' => 'postImages/test.png',
            'user_id' => $this->user->id,
        ]);
        Storage::putFileAs('postImages/', UploadedFile::fake()->image('file.png', 600, 600), 'test.png');
        $response = $this->put("/api/posts/$post->id", [
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'preview_text' => $this->faker->text,
            'detail_text' => $this->faker->text,
            'img' => UploadedFile::fake()->image('file.png', 600, 600),

        ], ['Accept' => 'application/json']);
        $response->assertStatus(200);
    }

    public function test_action_destroy(): void
    {
        Auth::login($this->user);
        $post = Post::create([
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'preview_text' => $this->faker->text,
            'detail_text' => $this->faker->text,
            'img_path' => 'postImages/test.png',
            'user_id' => $this->user->id,
        ]);
        $response = $this->delete("/api/posts/$post->id", [],
            ['Accept' => 'application/json']);
        $response->assertStatus(200);
    }

}
