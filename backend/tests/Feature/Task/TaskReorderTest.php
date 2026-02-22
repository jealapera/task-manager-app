<?php

use App\Models\Task;
use App\Models\User;

describe('POST /api/tasks/reorder (reorder)', function () {
    it('persists new sort order for user tasks', function () {
        $user = User::factory()->create();
        $taskA = Task::factory()->create(['user_id' => $user->id, 'sort_order' => 0]);
        $taskB = Task::factory()->create(['user_id' => $user->id, 'sort_order' => 1]);

        $this->actingAs($user)
            ->postJson('/api/tasks/reorder', [
                'tasks' => [
                    ['id' => $taskA->id, 'sort_order' => 1],
                    ['id' => $taskB->id, 'sort_order' => 0],
                ],
            ])
            ->assertOk()
            ->assertJson(['message' => 'Order saved.']);

        expect($taskA->fresh()->sort_order)->toBe(1)
            ->and($taskB->fresh()->sort_order)->toBe(0);
    });

    it('validates that tasks array is required', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/tasks/reorder', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tasks']);
    });

    it('validates each item has id and sort_order', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/tasks/reorder', [
                'tasks' => [['id' => 'abc']],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tasks.0.id', 'tasks.0.sort_order']);
    });

    it('requires authentication', function () {
        $this->postJson('/api/tasks/reorder', ['tasks' => []])
            ->assertUnauthorized();
    });
});
