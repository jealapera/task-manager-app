<?php

use App\Models\User;

describe('Login', function () {
    it('returns a token on valid credentials', function () {
        $user = User::factory()->create(['password' => bcrypt('secret')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    });

    it('rejects invalid credentials', function () {
        User::factory()->create(['email' => 'test@example.com']);

        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    });

    it('validates required fields', function () {
        $this->postJson('/api/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    });

    it('validates email format', function () {
        $this->postJson('/api/login', ['email' => 'not-an-email', 'password' => 'secret'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });
});

describe('Logout', function () {
    it('revokes the current token', function () {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJson(['message' => 'Logged out successfully.']);

        expect($user->tokens()->count())->toBe(0);
    });

    it('requires authentication', function () {
        $this->postJson('/api/logout')
            ->assertUnauthorized();
    });
});
