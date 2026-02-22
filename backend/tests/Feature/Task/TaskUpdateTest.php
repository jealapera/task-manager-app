<?php

use App\Models\Task;
use App\Models\User;

describe('PUT /api/tasks/{task} (update)', function () {
    it('updates the task statement', function () {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'statement' => 'Old statement']);

        $this->actingAs($user)
            ->putJson("/api/tasks/{$task->id}", ['statement' => 'Updated statement'])
            ->assertOk()
            ->assertJsonPath('data.statement', 'Updated statement');
    });

    it('toggles task status to completed', function () {
        $user = User::factory()->create();
        $task = Task::factory()->pending()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->putJson("/api/tasks/{$task->id}", ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.is_completed', true);
    });

    it('toggles task status back to pending', function () {
        $user = User::factory()->create();
        $task = Task::factory()->completed()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->putJson("/api/tasks/{$task->id}", ['status' => 'pending'])
            ->assertOk()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.is_completed', false);
    });

    it('rejects invalid status value', function () {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->putJson("/api/tasks/{$task->id}", ['status' => 'in_progress'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    });

    it('prevents a user from updating another user\'s task', function () {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)
            ->putJson("/api/tasks/{$task->id}", ['statement' => 'Hacked'])
            ->assertForbidden();
    });

    it('returns 404 for a non-existent task', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->putJson('/api/tasks/99999', ['statement' => 'Ghost'])
            ->assertNotFound();
    });

    it('requires authentication', function () {
        $task = Task::factory()->create();

        $this->putJson("/api/tasks/{$task->id}", ['statement' => 'No auth'])
            ->assertUnauthorized();
    });
});
