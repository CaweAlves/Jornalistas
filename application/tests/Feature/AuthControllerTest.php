<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

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

    public function testShouldBeAbleToLogoutOfTheApplication()
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

    public function testShouldBeAbleToRegisterANewUserInTheApplication()
    {
        $name = $this->faker->name;
        $email = $this->faker->unique()->freeEmail;

        $payload = [
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson('/api/register', $payload);
        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => $name,
            'email' => $email,
        ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertTrue(auth()->check());
        $this->assertEquals(User::first()->id, auth()->user()->id);
    }
}
