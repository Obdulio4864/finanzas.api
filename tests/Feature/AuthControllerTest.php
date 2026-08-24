<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_a_bearer_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ana López',
            'email' => 'ana@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'device_name' => 'react-web',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email'],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'ana@example.com']);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_user_can_login_and_only_logout_the_current_token(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123'),
        ]);

        $firstToken = $user->createToken('first-device')->plainTextToken;
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Password123',
            'device_name' => 'react-web',
        ]);

        $loginResponse->assertOk()->assertJsonStructure(['token', 'user']);

        $this->withToken($loginResponse->json('token'))
            ->postJson('/api/logout')
            ->assertNoContent();

        $this->withToken($firstToken)
            ->getJson('/api/egresos')
            ->assertOk();
    }
}
