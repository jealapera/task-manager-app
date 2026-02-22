<?php

use App\Models\Task;
use App\Models\User;

describe('DELETE /api/tasks/{task} (destroy)', function () {
    it('deletes the task and returns 204', function () {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    });

    it('prevents a user from deleting another user\'s task', function () {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    });

    it('returns 404 for a non-existent task', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson('/api/tasks/99999')
            ->assertNotFound();
    });

    it('requires authentication', function () {
        $task = Task::factory()->create();

        $this->deleteJson("/api/tasks/{$task->id}")
            ->assertUnauthorized();
    });
});
