<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CommentControllerTest extends TestCase
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
     * @var $post Post
     */
    private $post;
    /**
     * @var $commentTable string
     */
    private $commentTable;

    public function setup(): void
    {
        parent::setUp();
        $this->setUpFaker();
        Storage::fake(self::FAKE_DISK);
        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->post = Post::factory()->for($this->user)->create();
        $this->commentTable = (new Comment)->getTable();
    }

    public function test_action_index(): void
    {
        $routeUrl = route('comments.index', [], false);
        $blockedComment = Comment::factory()->for($this->post)->create();
        $moderatedComment = Comment::factory()->moderated()->for($this->post)->create();

        $response = $this->getJson($routeUrl);
        $response->assertOk()
            ->assertJson(function (AssertableJson $json) use ($blockedComment, $moderatedComment) {
                $json->has('comments.data', 1)
                    ->has('comments.data.0', function (AssertableJson $json) use ($blockedComment, $moderatedComment) {
                        $json->where('id', $moderatedComment->id)->etc();
                    });
            });

        $response = $this->actingAs($this->user)->getJson($routeUrl);
        $response->assertOk()
            ->assertJson(function (AssertableJson $json) use ($blockedComment, $moderatedComment) {
                $json->has('comments.data', 1)
                    ->has('comments.data.0', function (AssertableJson $json) use ($blockedComment, $moderatedComment) {
                        $json->where('id', $moderatedComment->id)->etc();
                    });
            });

        $response = $this->actingAs($this->admin)->getJson($routeUrl);
        $response->assertOk()
            ->assertJson(function (AssertableJson $json) use ($blockedComment, $moderatedComment) {
                $json->has('comments.data', 2)
                    ->has('comments.data.0', function (AssertableJson $json) use ($blockedComment) {
                        $json->where('id', $blockedComment->id)->etc();
                    })
                    ->has('comments.data.1', function (AssertableJson $json) use ($moderatedComment) {
                        $json->where('id', $moderatedComment->id)->etc();
                    });
            });

    }

    public function test_action_store_validation(): void
    {
        $routeUrl = route('comments.store', [], false);
        $response = $this->postJson($routeUrl);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name' => "The name field is required.",
                'email' => "The email field is required.",
                'post_id' => "The post id field is required."
            ]);
        $response = $this->postJson($routeUrl, [
            'name' => $this->faker->text(3000),
            'email' => $this->faker->name,
            'post_id' => 0,
        ]);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name' => "The name must not be greater than 255 characters.",
                'email' => "The email must be a valid email address.",
                'post_id' => "The selected post id is invalid."
            ]);
    }

    public function test_action_store(): void
    {
        $routeUrl = route('comments.store', [], false);
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'post_id' => $this->post->id,
        ];

        $response = $this->postJson($routeUrl, $data);
        $response->assertOk();
        $this->assertDatabaseHas($this->commentTable, $data + ['user_id' => null]);

        $response = $this->actingAs($this->user)->postJson($routeUrl, $data);
        $response->assertOk();
        $this->assertDatabaseHas($this->commentTable, $data + ['user_id' => $this->user->id]);


    }

    public function test_action_show(): void
    {
        $comment = Comment::factory()->for($this->post)->create();
        $routeUrl = route('comments.show', ['comment' => $comment->id]);

        $response = $this->getJson($routeUrl);
        $response->assertForbidden();

        $response = $this->actingAs($this->user)->getJson($routeUrl);
        $response->assertForbidden();

        $response = $this->actingAs($this->admin)->getJson($routeUrl);
        $response->assertOk()
            ->assertJsonFragment(['id' => $comment->id]);

        $comment = Comment::factory()->for($this->post)->for($this->user)->create();
        $routeUrl = route('comments.show', ['comment' => $comment->id]);

        $response = $this->actingAs($this->user)->getJson($routeUrl);
        $response->assertOk()
            ->assertJsonFragment(['id' => $comment->id]);
    }

    public function test_action_update_validation(): void
    {
        $comment = Comment::factory()->for($this->post)->create();
        $routeUrl = route('comments.update', ['comment' => $comment->id], false);

        $response = $this->actingAs($this->admin)->putJson($routeUrl, [
            'name' => $this->faker->text(3000),
            'email' => $this->faker->name
        ]);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name' => "The name must not be greater than 255 characters.",
                'email' => "The email must be a valid email address."
            ]);
    }

    public function test_action_update(): void
    {
        $comment = Comment::factory()->for($this->post)->create();
        $routeUrl = route('comments.update', ['comment' => $comment->id], false);
        $data = [
            'name' => $this->faker->text(),
            'email' => $this->faker->email()
        ];
        $response = $this->actingAs($this->admin)->putJson($routeUrl, $data);
        $response->assertOk();
        $this->assertDatabaseHas($this->commentTable, $data);

        $response = $this->actingAs($this->user)->putJson($routeUrl, $data);
        $response->assertForbidden();

        $response = $this->putJson($routeUrl, $data);
        $response->assertForbidden();

        $comment = Comment::factory()->for($this->post)->for($this->user)->create();
        $routeUrl = route('comments.update', ['comment' => $comment->id], false);
        $data = [
            'name' => $this->faker->text(),
            'email' => $this->faker->email()
        ];
        $response = $this->actingAs($this->user)->putJson($routeUrl, $data);
        $response->assertOk();
        $this->assertDatabaseHas($this->commentTable, $data);

        $comment = Comment::factory()->moderated()->for($this->post)->for($this->user)->create();
        $routeUrl = route('comments.update', ['comment' => $comment->id], false);
        $data = [
            'name' => $this->faker->text(),
            'email' => $this->faker->email()
        ];
        $response = $this->actingAs($this->user)->putJson($routeUrl, $data);
        $response->assertForbidden();
    }

    public function test_action_destroy(): void
    {
        $comment = Comment::factory()->for($this->post)->create();
        $routeUrl = route('comments.destroy', ['comment' => $comment->id], false);

        $response = $this->actingAs($this->user)->deleteJson($routeUrl);
        $response->assertForbidden();

        $response = $this->deleteJson($routeUrl);
        $response->assertForbidden();

        $response = $this->actingAs($this->admin)->deleteJson($routeUrl);
        $response->assertOk();
        $this->assertDatabaseMissing($this->commentTable, $comment->attributesToArray());
    }

    public function test_action_moderate(): void
    {
        $comment = Comment::factory()->for($this->post)->create();
        $routeUrl = route('comments.moderate', ['comment' => $comment->id], false);
        $data = ['is_moderated' => Comment::MODERATED];
        $response = $this->putJson($routeUrl, $data);
        $response->assertUnauthorized();

        $response = $this->actingAs($this->user)->putJson($routeUrl, $data);
        $response->assertOk();
        $this->assertDatabaseHas($this->commentTable, array_merge($comment->attributesToArray(), $data));
    }
}
