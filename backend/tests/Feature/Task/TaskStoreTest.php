<?php

use App\Models\User;

describe('POST /api/tasks (store)', function () {
    it('creates a task for the authenticated user', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/tasks', [
                'statement' => 'Prepare weekly report',
                'task_date' => '2026-02-20',
            ])
            ->assertCreated()
            ->assertJsonPath('data.statement', 'Prepare weekly report')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'statement' => 'Prepare weekly report',
        ]);
    });

    it('accepts optional priority', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/tasks', [
                'statement' => 'High priority task',
                'task_date' => '2026-02-20',
                'priority' => 3,
            ])
            ->assertCreated()
            ->assertJsonPath('data.priority', 3);
    });

    it('requires statement and task_date', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/tasks', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['statement', 'task_date']);
    });

    it('rejects statement longer than 500 characters', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/tasks', [
                'statement' => str_repeat('a', 501),
                'task_date' => '2026-02-20',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['statement']);
    });

    it('rejects invalid date format', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/tasks', [
                'statement' => 'Task',
                'task_date' => '20-02-2026',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['task_date']);
    });

    it('requires authentication', function () {
        $this->postJson('/api/tasks', ['statement' => 'Task', 'task_date' => '2026-02-20'])
            ->assertUnauthorized();
    });
});
