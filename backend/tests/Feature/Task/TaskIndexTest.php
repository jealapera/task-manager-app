<?php

use App\Models\Task;
use App\Models\User;

describe('GET /api/tasks (index)', function () {
    it('returns tasks for the authenticated user on the given date', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Task::factory()->create(['user_id' => $user->id, 'task_date' => '2026-02-20', 'statement' => 'My task']);
        Task::factory()->create(['user_id' => $other->id, 'task_date' => '2026-02-20', 'statement' => 'Their task']);

        $this->actingAs($user)
            ->getJson('/api/tasks?task_date=2026-02-20')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.statement', 'My task');
    });

    it('requires task_date when no search is provided', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/tasks')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['task_date']);
    });

    it('searches across all dates when task_date is omitted', function () {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'task_date' => '2026-01-10', 'statement' => 'Old dentist appointment']);
        Task::factory()->create(['user_id' => $user->id, 'task_date' => '2026-02-20', 'statement' => 'New dentist followup']);
        Task::factory()->create(['user_id' => $user->id, 'task_date' => '2026-02-20', 'statement' => 'Buy groceries']);

        $this->actingAs($user)
            ->getJson('/api/tasks?search=dentist')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('requires authentication', function () {
        $this->getJson('/api/tasks?task_date=2026-02-20')
            ->assertUnauthorized();
    });

    it('filters tasks by search keyword', function () {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'task_date' => '2026-02-20', 'statement' => 'Buy groceries']);
        Task::factory()->create(['user_id' => $user->id, 'task_date' => '2026-02-20', 'statement' => 'Call dentist']);

        $this->actingAs($user)
            ->getJson('/api/tasks?task_date=2026-02-20&search=groceries')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.statement', 'Buy groceries');
    });

    it('returns an empty list when no tasks exist for that date', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/tasks?task_date=2099-01-01')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('returns correct resource shape', function () {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'task_date' => '2026-02-20']);

        $this->actingAs($user)
            ->getJson('/api/tasks?task_date=2026-02-20')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'statement', 'status', 'is_completed', 'priority', 'task_date', 'sort_order']],
            ]);
    });
});
