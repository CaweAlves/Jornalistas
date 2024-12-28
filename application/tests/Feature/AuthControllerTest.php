<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testShouldBeAbleToLogin()
    {
        $user = User::factory()->create(
            [
                'password' => bcrypt($password = 'password')
            ]
        );

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token']);

        $this->assertEquals('Authorized', $response->json('message'));
        $this->assertTrue(auth()->check());
        $this->assertEquals($user->id, auth()->user()->id);
    }

    public function testUserCanLogout()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $token = JWTAuth::fromUser($user);

        $this->assertTrue(auth()->check());
        $this->assertEquals($user->id, auth()->user()->id);

        $response = $this->postJson(
            '/api/logout',
            [],
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(200);
        $this->assertFalse(auth()->check());
    }
}
