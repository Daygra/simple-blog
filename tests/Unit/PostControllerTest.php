<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;
use Tests\CreatesApplication;

class PostControllerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use CreatesApplication;
    /**
     * A basic test example.
     *
     * @return void
     */

    private $client;
    public function setup(): void
    {
        parent::setUp();
        $this->setUpFaker();

    }

    public function test_action_index(): void
    {
       $response =  Http::GET('http://127.0.0.1:8000/api/posts/');
       dd($response);
        $this->client->request('GET', 'posts');
        $response->assertStatus(200);
    }
}
