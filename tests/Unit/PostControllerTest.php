<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    const FAKE_DISK = 'public';

    /**
     * @var $user User
     */
    private $user;
    /**
     * @var $admin User
     */
    private $admin;
    /**
     * @var $postTable string
     */
    private $postTable;

    public function setup(): void
    {
        parent::setUp();
        $this->setUpFaker();
        Storage::fake(self::FAKE_DISK);
        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->postTable = (new Post)->getTable();
    }

    public function test_action_index(): void
    {
        $routeUrl = route('posts.index', [], false);
        $post = Post::factory()->for($this->user)->create();
        $response = $this->getJson($routeUrl);
        $response->assertOk()
            ->assertJson(function (AssertableJson $json) use ($post) {
                $json->has('posts.data', 1)
                    ->has('posts.data.0', function (AssertableJson $json) use ($post) {
                        $json->where('id', $post->id)->etc();
                    });
            });
    }

    public function test_action_store_validation(): void
    {
        $routeUrl = route('posts.store', [], false);
        $response = $this->actingAs($this->user)->postJson($routeUrl);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title' => "The title field is required.",
                'slug' => "The slug field is required.",
                'preview_text' => "The preview text field is required.",
                'detail_text' => "The detail text field is required.",
                'img' => "The img field is required."
            ]);

        $response = $this->actingAs($this->user)->postJson($routeUrl, [
            'title' => $this->faker->text(3000),
            'slug' => $this->faker->text(3000),
            'preview_text' => $this->faker->text(3000),
            'detail_text' => $this->faker->text(150000),
            'img' => UploadedFile::fake()->create('file.pdf'),
        ]);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title' => "The title must not be greater than 255 characters.",
                'slug' => "The slug must not be greater than 255 characters.",
                'preview_text' => "The preview text must not be greater than 255 characters.",
                'detail_text' => "The detail text must not be greater than 65535 characters.",
                'img' => "The img must be an image."
            ]);

        $slug = $this->faker->text(20);
        Post::factory()->for($this->user)->create(['slug' => $slug]);
        $response = $this->actingAs($this->user)->postJson($routeUrl, ['slug' => $slug]);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'slug' => "The slug has already been taken."
            ]);

    }

    public function test_action_store(): void
    {
        $routeUrl = route('posts.store', [], false);
        $response = $this->actingAs($this->user)->postJson($routeUrl, [
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'preview_text' => $this->faker->text,
            'detail_text' => $this->faker->text,
            'img' => UploadedFile::fake()->image('file.png', 20, 20),
        ]);
        $response->assertStatus(200);
        Storage::disk(self::FAKE_DISK)->assertExists($response->json('img_path'));
        $this->assertDatabaseHas($this->postTable, ['id' => $response->json('id')]);
    }

    public function test_action_show(): void
    {
        $post = Post::factory()->for($this->user)->create();
        $routeUrl = route('posts.show', ['post' => $post->id]);
        $response = $this->getJson($routeUrl);
        $response->assertOk()
            ->assertJsonFragment(['id' => $post->id]);
    }

    public function test_action_update_validation(): void
    {
        $slug = $this->faker->text(20);
        $post = Post::factory()->for($this->user)->create(['slug' => $slug]);
        $routeUrl = route('posts.update', ['post' => $post->id], false);
        $response = $this->actingAs($this->user)->putJson($routeUrl);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title' => "The title field is required.",
                'slug' => "The slug field is required.",
                'preview_text' => "The preview text field is required.",
                'detail_text' => "The detail text field is required.",
                'img' => "The img field is required."
            ]);

        $response = $this->actingAs($this->user)->putJson($routeUrl, [
            'title' => $this->faker->text(3000),
            'slug' => $this->faker->text(3000),
            'preview_text' => $this->faker->text(3000),
            'detail_text' => $this->faker->text(150000),
            'img' => UploadedFile::fake()->create('file.pdf'),
        ]);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title' => "The title must not be greater than 255 characters.",
                'slug' => "The slug must not be greater than 255 characters.",
                'preview_text' => "The preview text must not be greater than 255 characters.",
                'detail_text' => "The detail text must not be greater than 65535 characters.",
                'img' => "The img must be an image."
            ]);

        $response = $this->actingAs($this->user)->putJson($routeUrl, ['slug' => $slug]);
        $response->assertUnprocessable()
            ->assertValid(['slug']);

    }

    public function test_action_update(): void
    {
        $post = Post::factory()->for($this->user)->create();
        $routeUrl = route('posts.update', ['post' => $post->id], false);
        $data = [
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'preview_text' => $this->faker->text,
            'detail_text' => $this->faker->text,
            'img' => UploadedFile::fake()->image('file.png', 20, 20),
        ];
        $response = $this->actingAs($this->user)->putJson($routeUrl, $data);
        $response->assertOk();
        Storage::disk(self::FAKE_DISK)->assertExists($response->json('img_path'));
        Storage::disk(self::FAKE_DISK)->assertMissing($post->img_path);
        $this->assertDatabaseHas($this->postTable, ['id' => $response->json('id')]);

        $newUser = User::factory()->create();
        $response = $this->actingAs($newUser)->putJson($routeUrl, $data);
        $response->assertForbidden();

        $response = $this->actingAs($this->admin)->putJson($routeUrl, $data);
        $response->assertOk();

    }

    public function test_action_destroy(): void
    {
        $post = Post::factory()->for($this->user)->create();
        $routeUrl = route('posts.destroy', ['post' => $post->id], false);
        $response = $this->actingAs($this->user)->deleteJson($routeUrl);
        $response->assertForbidden();

        $response = $this->actingAs($this->admin)->deleteJson($routeUrl);
        $response->assertOk();
        Storage::disk(self::FAKE_DISK)->assertMissing($post->img_path);
        $this->assertDatabaseMissing($this->postTable, ['id' => $post->id]);
    }

}
